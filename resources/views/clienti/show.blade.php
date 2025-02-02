@if(auth()->user()->ruolo->nome === 'Cliente' || 
    (auth()->user()->ruolo->nome !== 'Amministratore' && 
    !$cliente->permessi->contains('id_utente', auth()->user()->id)))
    <script>
        window.location = "/";
    </script>
@endif

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dettagli') }} {{ $cliente->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900 rounded-lg">
                <!-- Header con logo e nome del cliente -->
                <div class="flex justify-between items-center mb-6">
                    @if(auth()->user()->ruolo->nome === 'Amministratore')
                        <a href="{{ route('clienti.edit', $cliente->id) }}" class="flex items-center block bg-white border border-gray-200 rounded-lg shadow p-4 hover:bg-gray-100 transition ease-in-out duration-150">
                            <div class="mr-4 p-3">
                                <img src="/logoClienti/{{ $cliente->logo_cliente }}" alt="Logo Cliente" class="w-16 h-16 object-cover rounded-full">
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold">{{ ucwords($cliente->nome) }}</h3>
                            </div>
                        </a>
                    @else
                        <div class="flex items-center block bg-white border border-gray-200 rounded-lg shadow p-4">
                            <div class="mr-4 p-3">
                                <img src="/logoClienti/{{ $cliente->logo_cliente }}" alt="Logo Cliente" class="w-16 h-16 object-cover rounded-full">
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold">{{ ucwords($cliente->nome) }}</h3>
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->ruolo->nome === 'Amministratore')
                        <a href="{{ route('assets.index', $cliente->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Gestisci Asset
                        </a>
                    @endif
                    <a href="{{ route('media_pubblicazioni.index', $cliente->id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Visualizza Media
                    </a>
                </div>

                <!-- Calendario delle pubblicazioni -->
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900 rounded-lg">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
                @vite(['resources/css/app.css', 'resources/js/app.js'])

                <script>
                    var calendarEvents = @json($calendarEvents);
                    window.calendarEvents = calendarEvents;
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
