<?php

namespace App\Http\Controllers;

use App\Models\MediaPubblicazione;
use App\Models\Cliente;
use Illuminate\Http\Request;

class MediaPubblicazioneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $clienteId
     * @return \Illuminate\Http\Response
     */
    public function index($clienteId)
    {
        $cliente = Cliente::with('permessi')->findOrFail($clienteId);
        $media = MediaPubblicazione::where('id_cliente', $clienteId)->get();
    
        return view('media_pubblicazioni.index', compact('media', 'cliente'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  int  $clienteId
     * @return \Illuminate\Http\Response
     */
    public function create($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        return view('media_pubblicazioni.create', compact('cliente'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $clienteId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $clienteId)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,wmv|max:20480',
        ]);
    
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('mediaPubblicazioni'), $fileName);
    
        $media = MediaPubblicazione::create([
            'nome' => $fileName,
            'id_cliente' => $clienteId,
        ]);
    
        return redirect()->route('media_pubblicazioni.index', $clienteId)->with('success', 'Media creato con successo.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MediaPubblicazione  $mediaPubblicazione
     * @return \Illuminate\Http\Response
     */
    public function show(MediaPubblicazione $mediaPubblicazione)
    {
        $cliente = Cliente::with('permessi')->findOrFail($mediaPubblicazione->id_cliente);
    
        // Verifica se l'utente ha i permessi per vedere questo media
        if (auth()->user()->ruolo->nome !== 'Amministratore' && 
            !$cliente->permessi->contains('id_utente', auth()->user()->id)) {
            return redirect('/')->with('error', 'Accesso negato');
        }
    
        return view('media_pubblicazioni.show', compact('mediaPubblicazione'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MediaPubblicazione  $mediaPubblicazione
     * @return \Illuminate\Http\Response
     */
    public function destroy(MediaPubblicazione $mediaPubblicazione)
    {
        $mediaPubblicazione->delete();

        return redirect()->route('media_pubblicazioni.index', $mediaPubblicazione->id_cliente)->with('success', 'Media eliminato con successo.');
    }
}
