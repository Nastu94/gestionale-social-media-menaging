<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatPubblicazione;
use App\Models\Pubblicazione;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChatPubblicazioneController extends Controller
{
    /**
     * Carica i commenti di una pubblicazione
     * 
     * @param int $pubblicazioneId
     * @return \Illuminate\Http\Response
     */
    public function index($pubblicazioneId)
    {
        $commenti = ChatPubblicazione::where('id_pubblicazione', $pubblicazioneId)
            ->orderBy('data_testo', 'asc')
            ->get();

        $commentiHtml = view('components.comments', compact('commenti'))->render();

        return response()->json(['commentiHtml' => $commentiHtml]);
    }

    /**
     * Memorizza una nuova risorsa nel database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $pubblicazioneId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $pubblicazioneId)
    {
        $request->validate([
            'commento' => 'required|string'
        ]);

        $pubblicazione = Pubblicazione::findOrFail($pubblicazioneId);
        $user = Auth::user();

        // Crea il commento
        $commento = ChatPubblicazione::create([
            'id_pubblicazione' => $pubblicazioneId,
            'utente' => $user->ruolo->nome === 'Cliente' ? 'Cliente' : 'AmicoWeb',
            'commento' => $request->commento,
            'data_testo' => now()
        ]);

        // Controlla se l'utente ha ruolo Cliente e cambia lo stato della pubblicazione
        if ($user->ruolo->nome === 'Cliente') {
            $pubblicazione->stato_id = 2; // Imposta lo stato a 2
            $pubblicazione->save();
        }

        // Restituisci il nuovo commento come JSON
        return response()->json([
            'success' => true,
            'commento' => $commento,
            'utente' => $user->ruolo->nome === 'Cliente' ? 'Cliente' : 'AmicoWeb',
            'nome_cliente' => $user->ruolo->nome === 'Cliente' ? $user->name : 'AmicoWeb',
            'data_formattata' => Carbon::parse($commento->data_testo)->format('d-m-Y H:i')
        ]);
    }   
}
