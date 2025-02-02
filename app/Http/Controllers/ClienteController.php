<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\User;
use App\Models\PacchettoPubblicazione;
use App\Models\PermessoCliente;
use Illuminate\Support\Str;

class ClienteController extends Controller
{
    /**
     * Visualizza un elenco delle risorse.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Recupera l'utente autenticato
        $user = auth()->user();
    
        // Recupera i clienti in base al ruolo dell'utente loggato
        if ($user->ruolo->nome === 'Amministratore') {
            $clienti = Cliente::with('pacchetto', 'permessi')->get();
        } else {
            $clienti = Cliente::whereHas('permessi', function ($query) use ($user) {
                $query->where('id_utente', $user->id);
            })->with('pacchetto', 'permessi')->get();
        }
    
        // Ritorna la vista 'clienti.index' passando i dati dei clienti
        return view('clienti.index', compact('clienti'));
    }

    /**
     * Mostra il form per creare una nuova risorsa.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Recupera tutti gli utenti con ruolo 'dipendente' o 'fotografo'
        $utenti = User::whereHas('ruolo', function ($query) {
            $query->whereIn('nome', ['Dipendente', 'Fotografo']);
        })->get();
    
        // Recupera tutti gli utenti con ruolo 'cliente'
        $clienti = User::whereHas('ruolo', function ($query) {
            $query->where('nome', 'Cliente');
        })->get();
    
        // Recupera tutti i pacchetti
        $pacchetti = PacchettoPubblicazione::all();
    
        // Ritorna la vista 'clienti.create' passando i dati degli utenti, dei clienti e dei pacchetti
        return view('clienti.create', compact('utenti', 'clienti', 'pacchetti'));
    }

    /**
     * Memorizza una nuova risorsa nel database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Valida i dati del form
        $request->validate([
            'nome' => 'required|string|max:255',
            'id_utente_cliente' => 'required|exists:users,id',
            'logo_cliente' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'pacchetto_id' => 'required|exists:pacchetto_pubblicazioni,id',
            'sito_web' => 'required|boolean',
            'id_utenti' => 'required|array|min:1',
            'id_utenti.*' => 'exists:users,id',
            'firma' => 'nullable|string',
            'cellulare' => 'nullable|string|max:50',
            'promptGPT' => 'nullable|string',
        ]);
    
        // Gestisci il caricamento del file
        $logoFileName = null;
        if ($request->hasFile('logo_cliente')) {
            $file = $request->file('logo_cliente');
            $logoFileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('logoClienti'), $logoFileName);
        }
    
        $token = Str::random(64);
    
        // Crea un nuovo cliente con i dati validati
        $cliente = Cliente::create([
            'nome' => $request->nome,
            'id_utente_cliente' => $request->id_utente_cliente,
            'logo_cliente' => $logoFileName,
            'pacchetto_id' => $request->pacchetto_id,
            'sito_web' => $request->sito_web,
            'firma' => $request->firma,
            'token' => $token,
            'cellulare' => $request->cellulare,
            'promptGPT' => $request->promptGPT,
        ]);
    
        // Salva i permessi cliente
        foreach ($request->id_utenti as $idUtente) {
            PermessoCliente::create([
                'id_cliente' => $cliente->id,
                'id_utente' => $idUtente,
            ]);
        }
    
        return redirect()->route('clienti.index')->with('success', 'Cliente creato con successo.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function show(Cliente $cliente)
    {
        // Recupera il cliente insieme alle pubblicazioni
        $cliente = Cliente::with('pubblicazioni')->find($cliente->id);
    
        // Prepara i dati per gli eventi del calendario
        $calendarEvents = $cliente->pubblicazioni->map(function ($pubblicazione) {
            return [
                'title' => $pubblicazione->cliente->nome, // Ora accessibile direttamente
                'start' => \Carbon\Carbon::parse($pubblicazione->data_pubblicazione)->format('Y-m-d\TH:i:s'),
                'classNames' => 'stato-' . $pubblicazione->stato_id,
                'url' => route('pubblicazioni.show', $pubblicazione->id)
            ];
        });
    
        // Carica i permessi insieme al cliente
        $cliente->load('permessi');  
    
        // Passa sia le pubblicazioni che gli eventi del calendario alla vista
        return view('clienti.show', compact('cliente', 'calendarEvents'));
    }    

    /**
     * Mostra il form per modificare la risorsa specificata.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function edit(Cliente $cliente)
    {
        // Recupera tutti gli utenti con ruolo 'dipendente' o 'fotografo'
        $utenti = User::whereHas('ruolo', function ($query) {
            $query->whereIn('nome', ['Dipendente', 'Fotografo']);
        })->get();

        // Recupera tutti gli utenti con ruolo 'cliente'
        $clienti = User::whereHas('ruolo', function ($query) {
            $query->where('nome', 'Cliente');
        })->get();

        // Recupera tutti i pacchetti
        $pacchetti = PacchettoPubblicazione::all();

        // Recupera gli utenti con permessi sul cliente
        $utentiSelezionati = $cliente->permessi->pluck('id_utente')->toArray();

        // Ritorna la vista 'clienti.edit' passando i dati del cliente, degli utenti, dei clienti, dei pacchetti e degli utenti selezionati
        return view('clienti.edit', compact('cliente', 'utenti', 'clienti', 'pacchetti', 'utentiSelezionati'));
    }

    /**
     * Aggiorna la risorsa specificata nel database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cliente $cliente)
    {
        // Valida i dati del form
        $request->validate([
            'nome' => 'required|string|max:255',
            'id_utente_cliente' => 'required|exists:users,id',
            'logo_cliente' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'pacchetto_id' => 'required|exists:pacchetto_pubblicazioni,id',
            'sito_web' => 'required|boolean',
            'id_utenti' => 'required|array|min:1',
            'id_utenti.*' => 'exists:users,id',
            'firma' => 'nullable|string',
            'cellulare' => 'nullable|string|max:50',
            'promptGPT' => 'nullable|string',
        ]);
    
        // Gestisci il caricamento del file
        if ($request->hasFile('logo_cliente')) {
            $file = $request->file('logo_cliente');
            $logoFileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('logoClienti'), $logoFileName);
            $cliente->logo_cliente = $logoFileName;
        }
    
        // Aggiorna i campi del cliente solo se presenti nella richiesta
        $cliente->nome = $request->input('nome', $cliente->nome);
        $cliente->id_utente_cliente = $request->input('id_utente_cliente', $cliente->id_utente_cliente);
        $cliente->pacchetto_id = $request->input('pacchetto_id', $cliente->pacchetto_id);
        $cliente->sito_web = $request->input('sito_web', $cliente->sito_web);
        $cliente->firma = $request->input('firma', $cliente->firma);
        $cliente->cellulare = $request->input('cellulare', $cliente->cellulare);
        $cliente->promptGPT = $request->input('promptGPT', $cliente->promptGPT);
    
        // Salva le modifiche al cliente
        $cliente->save();
    
        // Aggiorna i permessi
        $selectedUsers = $request->input('id_utenti', []);
        $currentUsers = $cliente->permessi->pluck('id_utente')->toArray();
    
        // Rimuovi i permessi non selezionati
        foreach (array_diff($currentUsers, $selectedUsers) as $userId) {
            PermessoCliente::where('id_cliente', $cliente->id)
                ->where('id_utente', $userId)
                ->delete();
        }
    
        // Aggiungi i nuovi permessi selezionati
        foreach (array_diff($selectedUsers, $currentUsers) as $userId) {
            PermessoCliente::create([
                'id_cliente' => $cliente->id,
                'id_utente' => $userId,
            ]);
        }
    
        return redirect()->route('clienti.index')->with('success', 'Cliente aggiornato con successo.');
    }
    
    /**
     * Rimuove la risorsa specificata dal database.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cliente $cliente)
    {
        // Elimina il cliente dal database
        $cliente->delete();

        // Reindirizza alla lista dei clienti con un messaggio di successo
        return redirect()->route('clienti.index')->with('success', 'Cliente eliminato con successo.');
    }
}
