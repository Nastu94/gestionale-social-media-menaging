<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PacchettoPubblicazione;

class PacchettoPubblicazioneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pacchetti = PacchettoPubblicazione::all();
        return view('pacchetti.index', compact('pacchetti'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pacchetti.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'numero_pubblicazioni' => 'required|integer',
            'costo' => 'required|numeric',
        ]);

        PacchettoPubblicazione::create($request->all());
        return redirect()->route('pacchetti.index')->with('success', 'Pacchetto pubblicazione creato con successo.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PacchettoPubblicazione  $pacchetto
     * @return \Illuminate\Http\Response
     */
    public function show(PacchettoPubblicazione $pacchetto)
    {
        return view('pacchetti.show', compact('pacchetto'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PacchettoPubblicazione  $pacchetto
     * @return \Illuminate\Http\Response
     */
    public function edit(PacchettoPubblicazione $pacchetto)
    {
        return view('pacchetti.edit', compact('pacchetto'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PacchettoPubblicazione  $pacchetto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PacchettoPubblicazione $pacchetto)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'numero_pubblicazioni' => 'required|integer',
            'costo' => 'required|numeric',
        ]);

        $pacchetto->update($request->all());
        return redirect()->route('pacchetti.index')->with('success', 'Pacchetto pubblicazione aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PacchettoPubblicazione  $pacchetto
     * @return \Illuminate\Http\Response
     */
    public function destroy(PacchettoPubblicazione $pacchetto)
    {
        $pacchetto->delete();
        return redirect()->route('pacchetti.index')->with('success', 'Pacchetto pubblicazione eliminato con successo.');
    }
}

