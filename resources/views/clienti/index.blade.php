@if(auth()->user()->ruolo->nome === 'Cliente')
    <script>
        window.location = "/";
    </script>
@endif

<x-app-layout>
    <x-slot name="header">
        <!-- Div separato per il pulsante, visibile solo agli amministratori -->
            <div class="flex justify-between mb-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Clienti') }}
                </h2>

                @if(auth()->user()->ruolo->nome === 'Amministratore')
                    <a href="{{ route('clienti.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Aggiungi Cliente
                    </a>
                @endif
            </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900">

                <!-- Griglia per i clienti -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($clienti as $cliente)
                        <!-- Mostra il cliente solo se l'utente è un amministratore o ha permessi sul cliente -->
                        @if(auth()->user()->ruolo->nome === 'Amministratore' || $cliente->permessi->contains('id_utente', auth()->user()->id))
                            @php
                                if(auth()->user()->ruolo->nome === 'Amministratore')
                                    $href = route('clienti.edit', $cliente->id);
                            @endphp
                            <a href="{{ $href }}" class="block bg-white border border-gray-200 rounded-lg shadow p-4 flex items-center hover:bg-gray-100 transition ease-in-out duration-150">
                                <div class="mr-4 p-4">
                                    <img src="/logoClienti/{{ $cliente->logo_cliente }}" alt="Logo Cliente" class="w-16 h-16 object-cover rounded-full">
                                </div>
                                <div class="p-4">
                                    <h5 class="text-lg font-semibold mb-2">{{ ucwords($cliente->nome) }}</h5>
                                    <p class="text-gray-600">{{ $cliente->pacchetto->nome }}</p>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
