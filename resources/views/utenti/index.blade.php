@if(auth()->user()->ruolo->nome !== 'Amministratore')
    <script>
        window.location = "/";
    </script>
@endif

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between mb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestione Utenti') }}
            </h2>

            <a href="{{ route('utenti.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Aggiungi Utente
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nome Utente
                                </th>
                                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ruolo
                                </th>
                                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dettagli
                                </th>
                                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Gestisci
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($utenti as $utente)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $utente->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $utente->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $utente->ruolo->nome }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($utente->ruolo->nome == 'Cliente')
                                            <ul>
                                                @foreach($utente->clientiAssociati as $cliente)
                                                    <li>Progetto: {{ $cliente->nome }}</li>
                                                    @foreach($cliente->permessi as $permesso)
                                                        @if($permesso->utente->ruolo->nome == 'Dipendente')
                                                            <li>Dipendente: {{ $permesso->utente->name }}</li>
                                                        @elseif($permesso->utente->ruolo->nome == 'Fotografo')
                                                            <li>Fotografo: {{ $permesso->utente->name }}</li>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            </ul>
                                        @else
                                            <ul>
                                                @foreach($utente->clienti as $cliente)
                                                    <li>Progetto: {{ $cliente->nome }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex justify-center space-x-3">
                                            <a href="{{ route('utenti.edit', $utente) }}" class="text-indigo-600 hover:text-indigo-900">
                                                <x-heroicon-o-pencil class="w-5 h-5" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
