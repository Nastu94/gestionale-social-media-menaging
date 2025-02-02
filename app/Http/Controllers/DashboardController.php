<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Pubblicazione;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Mostra la dashboard per l'utente autenticato.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $clienti = collect();
        $pubblicazioniCalendario = collect();
        $pubblicazioniLista = collect();

        // Reindirizza i fotografi alla pagina dei clienti
        if ($user->ruolo->nome === 'Fotografo') {
            return redirect()->route('clienti.index');
        }

        // Recupera clienti e pubblicazioni in base al ruolo dell'utente
        if ($user->ruolo->nome === 'Dipendente') {
            $clienti = Cliente::whereHas('permessi', function ($query) use ($user) {
                $query->where('id_utente', $user->id);
            })->get();
        
            $pubblicazioniCalendario = Pubblicazione::whereHas('cliente.permessi', function ($query) use ($user) {
                $query->where('id_utente', $user->id);
            })->with('media', 'stato', 'cliente')->get();
        
            $pubblicazioniLista = Pubblicazione::whereHas('cliente.permessi', function ($query) use ($user) {
                $query->where('id_utente', $user->id);
            })->latest()->with('media', 'stato', 'cliente')->get();
        } elseif ($user->ruolo->nome === 'Cliente') {
            $clienti = Cliente::where('id_utente_cliente', $user->id)->get();
        
            $pubblicazioniCalendario = Pubblicazione::whereHas('cliente', function ($query) use ($user) {
                $query->where('id_utente_cliente', $user->id);
            })->with('media', 'stato', 'cliente')->get();
        
            $pubblicazioniLista = Pubblicazione::whereHas('cliente', function ($query) use ($user) {
                $query->where('id_utente_cliente', $user->id);
            })->latest()->with('media', 'stato', 'cliente')->get();
        } elseif ($user->ruolo->nome === 'Amministratore') {
            $clienti = Cliente::all();
            $pubblicazioniCalendario = Pubblicazione::with('media', 'stato', 'cliente')->get();
            $pubblicazioniLista = Pubblicazione::latest()->with('media', 'stato', 'cliente')->get();
        }
        

        // Divide le pubblicazioni in chunks di 5 per il carosello
        $pubblicazioniChunks = $pubblicazioniLista->sortByDesc('data_pubblicazione')->chunk(5);

        // Imposta l'URL e il cliente per ciascuna pubblicazione del calendario
        $pubblicazioniCalendario->each(function ($pubblicazione) {
            $pubblicazione->url = route('pubblicazioni.show', ['pubblicazione' => $pubblicazione->id]);
        });        

        return view('dashboard', compact('clienti', 'pubblicazioniChunks', 'pubblicazioniCalendario'));
    }

    /**
     * Filtra le pubblicazioni per cliente selezionato e restituisce dati aggiornati per AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $clienteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterByCliente(Request $request, $clienteId = null)
    {
        if ($clienteId === 'all') {
            // Recupera tutte le pubblicazioni
            $pubblicazioni = Pubblicazione::with('media', 'stato', 'cliente')->get();
        } else {
            // Recupera le pubblicazioni del cliente selezionato
            $pubblicazioni = Pubblicazione::where('id_cliente', $clienteId)->with('media', 'stato', 'cliente')->get();
        }
        
        // Raggruppa le pubblicazioni in chunks di 5
        $pubblicazioniChunks = $pubblicazioni->sortByDesc('data_pubblicazione')->chunk(5);
        
        // Genera l'HTML per il carosello
        $caroselloHtml = view('components.publications-carousel', compact('pubblicazioniChunks'))->render();
        
        return response()->json([
            'pubblicazioniCalendario' => $pubblicazioni,
            'pubblicazioniLista' => $caroselloHtml,
        ]);        
    }      

    /**
     * Ottieni il dettaglio di una pubblicazione per visualizzazione AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View|string
     */
    public function getPublicationDetails(Request $request, $id)
    {
        // Trova la pubblicazione con media e cliente associati
        $pubblicazione = Pubblicazione::with(['media', 'cliente', 'commenti'])->findOrFail($id);

        // Restituisce solo il componente di dettaglio per AJAX
        return view('components.publication-details', compact('pubblicazione'))->render();
    }
}
