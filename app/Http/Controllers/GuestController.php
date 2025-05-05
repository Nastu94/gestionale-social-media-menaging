<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pubblicazione;
use App\Models\ChatPubblicazione;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    /**
     * Mostra le pubblicazioni in stato di valutazione per il cliente identificato dal token.
     *
     * @param string $token
     * @return \Illuminate\View\View
     */
    public function showPublications($token)
    {
        // Recupera il cliente tramite il token
        $cliente = Cliente::where('token', $token)->firstOrFail();

        // Supponiamo che le pubblicazioni "in stato di valutazione da parte del cliente" abbiano uno stato_id specifico.
        // Ad esempio: stato_id = 3 (solo come esempio, da definire in base alla logica dell'applicazione).
        $statoValutazioneId = 3; 

        // Recupera le pubblicazioni del cliente con quello stato
        $pubblicazioni = $cliente->pubblicazioni()->where('stato_id', $statoValutazioneId)->get();

        // Recupera il percorso base per i file da Nextcloud
        $baseUri = config('services.nextcloud.base_uri');

        // Modifica il nome dei file per rimuovere il percorso base
        $pubblicazioni->each(function ($pub) use ($baseUri) {
            $pub->media->each(function ($m) use ($baseUri) {
                // Rimuovi il percorso base dal nome del file
                $m->nome = str_replace($baseUri, '', $m->nome);
            });
        });

        // Ritorna la vista passando il cliente e le pubblicazioni
        return view('guest.publications', compact('cliente', 'pubblicazioni'));
    }

    /**
     * Accetta una pubblicazione.
     *
     * @param  \App\Models\Pubblicazione  $pubblicazione
     * @return \Illuminate\Http\RedirectResponse
     */
    public function accept(Pubblicazione $pubblicazione)
    {
        // Modifica lo stato della pubblicazione (ad esempio, accettato = 4)
        $pubblicazione->update(['stato_id' => 4]);

        return redirect()->back()->with('success', 'Pubblicazione accettata con successo.');
    }

    /**
     * Rifiuta una pubblicazione con motivazione.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pubblicazione  $pubblicazione
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, $pubblicazioneId)
    {
        $request->validate([
            'motivazione' => 'required|string|max:1000',
        ]);
    
        $pubblicazione = Pubblicazione::findOrFail($pubblicazioneId);
    
        // Crea un nuovo commento nella tabella `chat_pubblicazioni` per salvare la motivazione del rifiuto
        ChatPubblicazione::create([
            'id_pubblicazione' => $pubblicazioneId,
            'utente' => 'Cliente', // O qualsiasi altro identificativo per gli utenti guest
            'commento' => 'Motivazione del rifiuto: ' . $request->motivazione,
            'data_testo' => now(),
        ]);
    
        // Aggiorna lo stato della pubblicazione a "Rifiutato"
        $pubblicazione->update([
            'stato_id' => 2,
        ]);
    
        return redirect()->back()->with('success', 'La pubblicazione è stata rifiutata e la motivazione è stata salvata.');
    }
    
}
