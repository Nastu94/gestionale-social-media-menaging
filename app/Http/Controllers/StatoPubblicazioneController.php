<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StatoPubblicazione;

class StatoPubblicazioneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stati = StatoPubblicazione::all();
        return view('stati.index', compact('stati'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('stati.create');
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
            'nome_stato' => 'required|string|max:255',
        ]);

        StatoPubblicazione::create($request->all());
        return redirect()->route('stati.index')->with('success', 'Stato pubblicazione creato con successo.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StatoPubblicazione  $stato
     * @return \Illuminate\Http\Response
     */
    public function show(StatoPubblicazione $stato)
    {
        return view('stati.show', compact('stato'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StatoPubblicazione  $stato
     * @return \Illuminate\Http\Response
     */
    public function edit(StatoPubblicazione $stato)
    {
        return view('stati.edit', compact('stato'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StatoPubblicazione  $stato
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StatoPubblicazione $stato)
    {
        $request->validate([
            'nome_stato' => 'required|string|max:255',
        ]);

        $stato->update($request->all());
        return redirect()->route('stati.index')->with('success', 'Stato pubblicazione aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StatoPubblicazione  $stato
     * @return \Illuminate\Http\Response
     */
    public function destroy(StatoPubblicazione $stato)
    {
        $stato->delete();
        return redirect()->route('stati.index')->with('success', 'Stato pubblicazione eliminato con successo.');
    }
}
