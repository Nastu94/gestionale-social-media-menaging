<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Pubblicazione;
use App\Models\MediaPubblicazione;
use App\Models\MediaInPubblicazione;
use App\Models\StatoPubblicazione;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PubblicazioneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
    
        if ($user->ruolo->nome === 'Dipendente') {
            // Recupera tutte le pubblicazioni collegate al dipendente autenticato
            $pubblicazioni = Pubblicazione::whereHas('cliente.permessi', function ($query) use ($user) {
                $query->where('id_utente', $user->id);
            })->with('cliente', 'media', 'stato')->get();
        } elseif ($user->ruolo->nome === 'Cliente') {
            // Recupera tutte le pubblicazioni collegate al cliente autenticato
            $pubblicazioni = Pubblicazione::whereHas('cliente.permessi', function ($query) use ($user) {
                $query->where('id_utente', $user->id);
            })->with('cliente', 'media', 'stato')->get();
        } else {
            // Reindirizza alla dashboard con un messaggio di errore se l'utente non è autorizzato
            return redirect()->route('dashboard')->with('error', 'Accesso negato');
        }
    
        // Aggiungi l'URL della rotta a ciascuna pubblicazione
        $pubblicazioni->each(function ($pubblicazione) {
            $pubblicazione->url = route('pubblicazioni.show', ['pubblicazione' => $pubblicazione->id]);
        });
    
        // Ritorna la vista 'pubblicazioni.index' passando i dati delle pubblicazioni
        return view('pubblicazioni.index', compact('pubblicazioni'));
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $clienteId)
    {
        $cliente = Cliente::with('permessi')->findOrFail($clienteId);
        $mediaIds = $request->query('media');
        $media = MediaPubblicazione::whereIn('id', explode(',', $mediaIds))->get();
        
        // Verifica se l'utente ha i permessi per creare una pubblicazione per questo cliente
        if (auth()->user()->ruolo->nome === 'Cliente' || auth()->user()->ruolo->nome === 'Fotografo' ||
            (auth()->user()->ruolo->nome !== 'Amministratore' && !$cliente->permessi->contains('id_utente', auth()->user()->id))) {
            return redirect('/')->with('error', 'Accesso negato');
        }
        
        return view('pubblicazioni.create', compact('cliente', 'media'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clienti,id',
            'selected_files' => 'nullable|array',
            'selected_files.*' => 'nullable|string',
            'testo' => 'nullable|string',
            'data_pubblicazione' => 'required|date',
            'note' => 'required|string',
        ]);
    
    
        // Imposta lo stato in base all'azione selezionata
        $statoId = $request->input('azione') === 'invia' ? 3 : 1;
    
        $pubblicazione = Pubblicazione::create([
            'id_cliente' => $request->cliente_id,
            'stato_id' => $statoId,
            'testo' => $request->testo,
            'data_pubblicazione' => $request->data_pubblicazione,
            'note' => $request->note,
        ]);
        
        $selectedFiles = $request->input('selected_files', []);
        
        $proxyPrefix     = rtrim(config('services.proxy.prefix'), '/') . '/';
        $nextcloudPrefix = rtrim(config('services.nextcloud.base_uri'), '/') . '/';
        
        foreach ($selectedFiles as $filePath) {
        
            // Se il file è stato caricato tramite proxy, riscrivilo verso Nextcloud
            if (Str::startsWith($filePath, $proxyPrefix)) {
                // Rimuoviamo la parte "https://clienti.sodanoconsulting.it/file/"
                $relativePath = Str::after($filePath, $proxyPrefix);
                // Ora costruiamo l'URL Nextcloud
                $filePath     = $nextcloudPrefix . ltrim($relativePath, '/');
            }
            
            $media = MediaPubblicazione::create([
                'nome'       => $filePath,
                'id_cliente' => $request->cliente_id,
            ]);
        
            MediaInPubblicazione::create([
                'id_media'        => $media->id,
                'id_pubblicazione'=> $pubblicazione->id,
            ]);
        }
    
        return redirect()->route('dashboard')->with('success', 'Pubblicazione creata con successo.');
    }

    /**
     * Genera un testo tramite API di OpenAI (GPT) basandosi sulle note inserite dall'utente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  L'identificativo della pubblicazione
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateGpt(Request $request, $id)
    {   
        // Validazione della richiesta
        $pubblicazione = Pubblicazione::findOrFail($id);
        $note          = $request->input('note');
    
        $apiKey = config('services.chat_gpt.api_key');

        // Verifica se la chiave API è presente
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'OpenAI API key mancante: verifica .env e config cache'
            ], 500);
        }
        
        $messages = [
            [
                'role'    => 'system',
                'content' => $pubblicazione->cliente->promptGPT
                             ?? 'Genera un testo creativo di massimo 100 parole in base alle note',
            ],
            [
                'role'    => 'user',
                'content' => "Note: $note",
            ],
        ];
        
        $payload = [
            'model'      => 'gpt-3.5-turbo',
            'messages'   => $messages,
            'max_tokens' => 300,
        ];
        
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
    
            return response()->json([
                'error' => "Errore nella generazione del testo: $error",
            ], 500);
        }
        

        curl_close($ch);
        $data = json_decode($response, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return response()->json([
                'error' => 'Errore nella risposta API: '.json_encode($data),
            ], 500);
        }

        // Salva il testo generato nella pubblicazione
        return response()->json([
            'generatedText' => $data['choices'][0]['message']['content'],
        ]);
    }
    

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pubblicazione  $pubblicazione
     * @return \Illuminate\Http\Response
     */
    public function show(Pubblicazione $pubblicazione)
    {
        $pubblicazione->load('stato', 'media');
    
        return view('pubblicazioni.show', compact('pubblicazione'));
    }    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pubblicazione  $pubblicazione
     * @return \Illuminate\Http\Response
     */
    public function edit(Pubblicazione $pubblicazione)
    {
        // Carica i dati della pubblicazione con i relativi media, stato, cliente e commenti della chat
        $pubblicazione->load('media', 'stato', 'cliente', 'commenti');

        // Ritorna la vista 'pubblicazioni.edit' passando i dati della pubblicazione inclusi i commenti
        return view('pubblicazioni.edit', compact('pubblicazione'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pubblicazione  $pubblicazione
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pubblicazione $pubblicazione)
    {
    
        // Valida i dati del form
        $validated = $request->validate([
            'selected_files' => 'nullable|array',
            'selected_files.*' => 'required|string',
            'testo' => 'required|string',
            'data_pubblicazione' => 'required|date',
        ]);
    
        // Imposta lo stato in base all'azione selezionata
        switch ($request->input('azione')) {
            case 'approva':
                $statoId = 4;
                break;
            case 'invia_al_cliente':
                $statoId = 3;
                break;
            default:
                $statoId = $pubblicazione->stato_id;
        }
    
        // Aggiorna i campi della pubblicazione
        $pubblicazione->update([
            'testo' => $request->input('testo'),
            'data_pubblicazione' => $request->input('data_pubblicazione'),
            'stato_id' => $statoId,
        ]);
    
        $selectedFiles = $request->input('selected_files', []);

        $proxyPrefix     = rtrim(config('services.proxy.prefix'), '/') . '/';
        $nextcloudPrefix = rtrim(config('services.nextcloud.base_uri'), '/') . '/';
    
        // 1. Per ogni file controllo se esiste già nella tabella media_pubblicazioni. 
        // In caso contrario lo creo. Poi ottengo l'id.
        $mediaIds = [];
        foreach ($selectedFiles as $filePath) {

            // Se il file è stato caricato tramite proxy, riscrivilo verso Nextcloud
            if (Str::startsWith($filePath, $proxyPrefix)) {
                // Rimuoviamo la parte "https://clienti.sodanoconsulting.it/file/"
                $relativePath = Str::after($filePath, $proxyPrefix);
                // Ora costruiamo l'URL Nextcloud
                $filePath     = $nextcloudPrefix . ltrim($relativePath, '/');
            }

            // Controlla se il media esiste già
            $media = MediaPubblicazione::where('nome', $filePath)->first();

            // Se il media non esiste già, lo creiamo
            if (!$media) {    
                // Se non ci sono media esistenti, dobbiamo capire come recuperare l'id_cliente.
                // Potresti aver bisogno di passare l'id_cliente come input nascosto se non sempre presente.
                // Per ora assumiamo che la pubblicazione abbia almeno un media, altrimenti va gestita diversamente.
                $clienteId = $pubblicazione->id_cliente;
                // Se non riesci a recuperare l'id_cliente, logghiamo un errore
                if (!$clienteId) {
                    // Se cliente_id non può essere recuperato, logghiamo un errore
                    Log::error('Impossibile recuperare cliente_id per creare media_pubblicazioni', ['file' => $filePath, 'pubblicazione_id' => $pubblicazione->id]);
                    // Potresti decidere di ritornare un errore JSON o gestirlo diversamente
                }
                
                // Crea un nuovo media_pubblicazione
                $media = MediaPubblicazione::create([
                    'nome' => $filePath,
                    'id_cliente' => $clienteId,
                ]);
            } else {
                // Se il media esiste già, logghiamo un messaggio informativo
                Log::info('Media esistente trovato', ['media_id' => $media->id, 'file' => $filePath]);
            }
            $mediaIds[] = $media->id;
        }
    
        // 2. Elimino tutti i file nella tabella media_in_pubblicazioni per questa pubblicazione
        MediaInPubblicazione::where('id_pubblicazione', $pubblicazione->id)->delete();
    
        // 3. Aggiunge i nuovi file nella tabella media_in_pubblicazioni
        foreach ($mediaIds as $mId) {
            MediaInPubblicazione::create([
                'id_media' => $mId,
                'id_pubblicazione' => $pubblicazione->id,
            ]);
        }
    
        // Restituisce i dati aggiornati
        // Log per vedere quante pubblicazioni abbiamo
        $pubblicazioniCalendario = Pubblicazione::with('media.cliente')->get();
    
        // Log per capire quali pubblicazioni non hanno cliente
        foreach ($pubblicazioniCalendario as $p) {
            if ($p->media->count() == 0 || !$p->media->first()->cliente) {
                Log::warning('Pubblicazione senza media o senza cliente', ['pubblicazione_id' => $p->id]);
            }
        }
    
        $pubblicazioniLista = view('components.publications-carousel', [
            'pubblicazioniChunks' => $pubblicazioniCalendario->sortByDesc('data_pubblicazione')->chunk(5),
        ])->render();
    
    
        return response()->json([
            'success' => true,
            'stato_id' => $statoId,
            'pubblicazioniCalendario' => $pubblicazioniCalendario,
            'pubblicazioniLista' => $pubblicazioniLista,
        ]);
    }
    
    /**
     * Pianifica una pubblicazione.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function pianifica(Request $request, $id)
    {
        $pubblicazione = Pubblicazione::findOrFail($id);
    
        // Controlla che lo stato attuale sia 4 (Approvata)
        if ($pubblicazione->stato_id != 4) {
            // Se la richiesta è AJAX, restituisci JSON con errore
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La pubblicazione non è pronta per essere pianificata.'
                ], 400);
            }
            // Altrimenti, usa il redirect con flash message
            return redirect()->route('dashboard', $id)->with('error', 'La pubblicazione non è pronta per essere pianificata.');
        }
    
        $pubblicazione->stato_id = 5; // Imposta lo stato a "Pianificata"
        $pubblicazione->save();
    
        // Se la richiesta è AJAX, restituisce un JSON di successo
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
    
        // Altrimenti, reindirizza alla dashboard con un messaggio di successo
        return redirect()->route('dashboard', $id)->with('success', 'Pubblicazione pianificata con successo.');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pubblicazione  $pubblicazione
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pubblicazione $pubblicazione)
    {
        // Elimina la pubblicazione dal database
        $pubblicazione->delete();

        // Reindirizza alla lista delle pubblicazioni con un messaggio di successo
        return redirect()->route('pubblicazioni.index')->with('success', 'Pubblicazione eliminata con successo.');
    }
}
