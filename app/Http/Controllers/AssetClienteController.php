<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetCliente;
use App\Models\Cliente;

class AssetClienteController extends Controller
{
    /**
     * Mostra l'elenco degli asset per un cliente specifico.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function index(Cliente $cliente)
    {
        // Recupera gli asset associati al cliente
        $assets = $cliente->assets;
        return view('assetsClienti.index', compact('cliente', 'assets'));
    }

    /**
     * Mostra il modulo per creare un nuovo asset per un cliente specifico.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function create(Cliente $cliente)
    {
        return view('assetsClienti.create', compact('cliente'));
    }

    /**
     * Memorizza un nuovo asset nel database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Cliente $cliente)
    {
        // Valida i dati del form
        $request->validate([
            'nome_assets' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        // Crea un nuovo asset associato al cliente
        AssetCliente::create([
            'id_cliente' => $cliente->id,
            'nome_assets' => $request->nome_assets,
            'username' => $request->username,
            'password' => $request->password,
        ]);

        // Reindirizza alla lista degli asset con un messaggio di successo
        return redirect()->route('assets.index', $cliente)->with('success', 'Asset creato con successo.');
    }

    /**
     * Mostra il modulo per modificare un asset esistente.
     *
     * @param  \App\Models\Cliente  $cliente
     * @param  \App\Models\AssetCliente  $asset
     * @return \Illuminate\Http\Response
     */
    public function edit(Cliente $cliente, AssetCliente $asset)
    {
        return view('assetsClienti.edit', compact('cliente', 'asset'));
    }

    /**
     * Aggiorna un asset esistente nel database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cliente  $cliente
     * @param  \App\Models\AssetCliente  $asset
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cliente $cliente, AssetCliente $asset)
    {
        // Valida i dati del form
        $request->validate([
            'nome_assets' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        // Aggiorna l'asset con i nuovi dati
        $asset->update($request->all());

        // Reindirizza alla lista degli asset con un messaggio di successo
        return redirect()->route('assets.index', $cliente)->with('success', 'Asset aggiornato con successo.');
    }

    /**
     * Rimuove un asset dal database.
     *
     * @param  \App\Models\Cliente  $cliente
     * @param  \App\Models\AssetCliente  $asset
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cliente $cliente, AssetCliente $asset)
    {
        // Elimina l'asset dal database
        $asset->delete();
        return redirect()->route('assets.index', $cliente)->with('success', 'Asset eliminato con successo.');
    }
}
