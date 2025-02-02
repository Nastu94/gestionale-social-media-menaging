<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ruolo;

class RuoloController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Recupera tutti i ruoli dal database
        $ruoli = Ruolo::all();
        
        // Ritorna la vista con i ruoli
        return view('ruoli.index', compact('ruoli'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Ritorna la vista per creare un nuovo ruolo
        return view('ruoli.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Valida i dati in arrivo dalla richiesta
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        // Crea un nuovo ruolo nel database
        Ruolo::create($request->all());

        // Reindirizza alla lista dei ruoli con un messaggio di successo
        return redirect()->route('ruoli.index')->with('success', 'Ruolo creato con successo.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ruolo  $ruolo
     * @return \Illuminate\Http\Response
     */
    public function show(Ruolo $ruolo)
    {
        // Ritorna la vista con i dettagli del ruolo
        return view('ruoli.show', compact('ruolo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ruolo  $ruolo
     * @return \Illuminate\Http\Response
     */
    public function edit(Ruolo $ruolo)
    {
        // Ritorna la vista per modificare il ruolo
        return view('ruoli.edit', compact('ruolo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ruolo  $ruolo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ruolo $ruolo)
    {
        // Valida i dati in arrivo dalla richiesta
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        // Aggiorna il ruolo con i nuovi dati
        $ruolo->update($request->all());

        // Reindirizza alla lista dei ruoli con un messaggio di successo
        return redirect()->route('ruoli.index')->with('success', 'Ruolo aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ruolo  $ruolo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ruolo $ruolo)
    {
        // Elimina il ruolo dal database
        $ruolo->delete();

        // Reindirizza alla lista dei ruoli con un messaggio di successo
        return redirect()->route('ruoli.index')->with('success', 'Ruolo eliminato con successo.');
    }
}
