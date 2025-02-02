<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermessoCliente;
use App\Models\Cliente;
use App\Models\User;

class PermessoClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permessi = PermessoCliente::with(['cliente', 'utente'])->get();
        return view('permessi.index', compact('permessi'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $clienti = Cliente::all();
        $utenti = User::all();
        return view('permessi.create', compact('clienti', 'utenti'));
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
            'id_cliente' => 'required|exists:clienti,id',
            'id_utente' => 'required|exists:users,id',
        ]);

        PermessoCliente::create($request->all());

        return redirect()->route('permessi.index')->with('success', 'Permesso creato con successo.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PermessoCliente  $permessoCliente
     * @return \Illuminate\Http\Response
     */
    public function show(PermessoCliente $permessoCliente)
    {
        return view('permessi.show', compact('permessoCliente'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PermessoCliente  $permessoCliente
     * @return \Illuminate\Http\Response
     */
    public function edit(PermessoCliente $permessoCliente)
    {
        $clienti = Cliente::all();
        $utenti = User::all();
        return view('permessi.edit', compact('permessoCliente', 'clienti', 'utenti'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PermessoCliente  $permessoCliente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PermessoCliente $permessoCliente)
    {
        $request->validate([
            'id_cliente' => 'required|exists:clienti,id',
            'id_utente' => 'required|exists:users,id',
        ]);

        $permessoCliente->update($request->all());

        return redirect()->route('permessi.index')->with('success', 'Permesso aggiornato con successo.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PermessoCliente  $permessoCliente
     * @return \Illuminate\Http\Response
     */
    public function destroy(PermessoCliente $permessoCliente)
    {
        $permessoCliente->delete();

        return redirect()->route('permessi.index')->with('success', 'Permesso eliminato con successo.');
    }
}
