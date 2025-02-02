<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Ruolo;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Carica gli utenti ordinati per ruolo e con le relazioni ruolo e clienti associati
        $utenti = User::with(['ruolo', 'clientiAssociati'])
                      ->orderBy('ruolo_id')
                      ->orderBy('name') // Ordina per id del ruolo
                      ->get();
    
        return view('utenti.index', compact('utenti'));
    }    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Carica i ruoli per il form di creazione dell'utente
        $ruoli = Ruolo::all();
        return view('utenti.create', compact('ruoli'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Valida i dati in arrivo
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'ruolo_id' => 'required|exists:ruoli,id',
        ]);

        // Crea un nuovo utente
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'ruolo_id' => $request->ruolo_id,
        ]);

        // Reindirizza alla lista degli utenti con un messaggio di successo
        return redirect()->route('utenti.index')->with('success', 'Utente creato con successo.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $utente
     * @return \Illuminate\Http\Response
     */
    public function edit(User $utenti)
    {
        // Carica i ruoli per il form di modifica dell'utente
        $ruoli = Ruolo::all();
        // Carica anche il ruolo associato all'utente
        $utenti->load('ruolo');
        return view('utenti.edit', compact('utenti', 'ruoli'));
    }    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $utente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $utenti)
    {
        // Validazione
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $utenti->id,
            'password' => 'nullable|string|min:8|confirmed',
            'ruolo_id' => 'required|exists:ruoli,id'
        ]);
    
        // Aggiornamento dei dati
        $utenti->name = $request->input('name');
        $utenti->email = $request->input('email');
        if ($request->filled('password')) {
            $utenti->password = Hash::make($request->input('password'));
        }
        $utenti->ruolo_id = $request->input('ruolo_id');
        $utenti->save();
    
        // Redirect con messaggio di successo
        return redirect()->route('utenti.index')->with('success', 'Utente aggiornato con successo.');
    }
    
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $utente
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $utente)
    {
        // Elimina l'utente
        $utente->delete();
        return redirect()->route('utenti.index')->with('success', 'Utente eliminato con successo.');
    }
}
