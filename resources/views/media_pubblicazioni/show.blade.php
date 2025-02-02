@if(auth()->user()->ruolo->nome === 'Cliente' || 
    (auth()->user()->ruolo->nome !== 'Amministratore' && 
    !$mediaPubblicazione->cliente->permessi->contains('id_utente', auth()->user()->id)))
    <script>
        window.location = "/";
    </script>
@endif

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dettagli Media') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900 rounded-lg">
                <div class="flex justify-center items-center mb-6">
                    <a href="{{ route('media_pubblicazioni.index', $mediaPubblicazione->cliente->id) }}" class="flex items-center block bg-white border border-gray-200 rounded-lg shadow p-4">
                        <div class="mr-4 p-3">
                            <img src="/logoClienti/{{ $mediaPubblicazione->cliente->logo_cliente }}" alt="Logo Cliente" class="w-16 h-16 object-cover rounded-full">
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold">{{ ucwords($mediaPubblicazione->cliente->nome) }}</h3>
                        </div>
                    </a>
                </div>
                <!-- Dettagli del media -->
                <div class="flex items-center mb-6">
                    @if(preg_match('/\.(jpeg|png|jpg|gif)$/i', $mediaPubblicazione->nome))
                        <img src="/mediaPubblicazioni/{{ $mediaPubblicazione->nome }}" alt="Media" class="object-cover rounded-lg w-full h-full">
                    @elseif(preg_match('/\.(mp4|mov|avi|wmv)$/i', $mediaPubblicazione->nome))
                        <video controls class="w-full h-auto">
                            <source src="/mediaPubblicazioni/{{ $mediaPubblicazione->nome }}" type="video/mp4">
                            Il tuo browser non supporta il tag video.
                        </video>
                    @else
                        File non supportato
                    @endif
                </div>

                @if(auth()->user()->ruolo->nome === 'Dipendente' || auth()->user()->ruolo->nome === 'Amministratore')
                    <div>
                        <a href="{{ route('pubblicazioni.create', ['cliente' => $mediaPubblicazione->cliente->id, 'media' => $mediaPubblicazione->id]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Pubblica
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
