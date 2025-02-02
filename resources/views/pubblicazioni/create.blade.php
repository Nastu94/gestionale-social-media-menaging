{{-- Verifica se l'utente ha i permessi necessari --}}
@if(auth()->user()->ruolo->nome === 'Cliente' || 
    auth()->user()->ruolo->nome === 'Fotografo' ||
    (auth()->user()->ruolo->nome !== 'Amministratore' && 
    !auth()->user()->permessi()->where('id_cliente', $cliente->id)->exists()))
    <script>
        window.location = "/";
    </script>
@endif

<x-app-layout>
    <x-slot name="header">
        {{-- Titolo della pagina --}}
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crea Pubblicazione') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900 rounded-lg">
                {{-- Sezione informazioni cliente (logo e nome) --}}
                <div class="flex justify-center items-center mb-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center block bg-white border border-gray-200 rounded-lg shadow p-4">
                        <div class="mr-4 p-3">
                            <img src="/logoClienti/{{ $cliente->logo_cliente }}" alt="Logo Cliente" class="w-16 h-16 object-cover rounded-full">
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold">{{ ucwords($cliente->nome) }}</h3>
                        </div>
                    </a>
                </div>

                {{-- Form per la creazione della pubblicazione --}}
                <form action="{{ route('pubblicazioni.store') }}" method="POST">
                    @csrf

                    {{-- ID cliente --}}
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">


                    {{-- Contenitore per input:hidden dei file selezionati --}}
                    <div id="selected-files-container"></div>

                    {{-- Non usiamo più media proveniente dal server, inizialmente nessun media --}}
                    {{-- Il carosello verrà mostrato solo se media.length > 0 --}}
                    <div x-data="{ 
                            currentIndex: 0, 
                            media: [], 
                            addFiles(files) { 
                                files.forEach(file => this.media.push({ nome: file })); 
                            },
                            setFiles(files) {
                                this.media = files.map(file => ({ nome: file }));
                                this.currentIndex = 0; // Reset del carosello alla prima immagine
                            }
                        }" 
                        x-ref="carouselComponent" 
                        class="relative mb-4" 
                        x-show="media.length > 0">

                        <template x-for="(mediaItem, index) in media" :key="index">
                            <div x-show="currentIndex === index" class="w-full h-80 flex items-center justify-center">
                                {{-- Immagine --}}
                                <template x-if="mediaItem.nome.match(/\.(jpeg|png|jpg|gif)$/i)">
                                    <img :src="mediaItem.nome" alt="Media della Pubblicazione" class="object-contain h-full w-full rounded-lg">
                                </template>
                                {{-- Video --}}
                                <template x-if="mediaItem.nome.match(/\.(mp4|mov|avi|wmv)$/i)">
                                    <video controls class="object-contain h-full w-full rounded-lg">
                                        <source :src="mediaItem.nome" type="video/mp4">
                                        Il tuo browser non supporta il tag video.
                                    </video>
                                </template>
                            </div>
                        </template>

                        {{-- Pulsanti per il carosello --}}
                        <button type="button" 
                                @click="currentIndex = (currentIndex > 0) ? currentIndex - 1 : media.length - 1" 
                                class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-500 text-white px-2 py-1 rounded-l">
                            <x-heroicon-s-chevron-left class="w-6 h-6" />
                        </button>
                        <button type="button" 
                                @click="currentIndex = (currentIndex < media.length - 1) ? currentIndex + 1 : 0" 
                                class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-500 text-white px-2 py-1 rounded-r">
                            <x-heroicon-s-chevron-right class="w-6 h-6" />
                        </button>
                    </div>


                    {{-- Pulsante per aprire il modale di selezione file Nextcloud --}}
                    <div class="mb-4">
                        <button type="button" id="browse-nextcloud" class="mt-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Sfoglia Nextcloud
                        </button>
                    </div>

                    {{-- Campo Testo --}}
                    <div class="mb-4">
                        <label for="testo" class="block text-gray-700 text-sm font-bold mb-2">Testo della Pubblicazione:</label>
                        <textarea name="testo" id="testo" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>

                    {{-- Campo Data e Ora --}}
                    <div class="mb-4">
                        <label for="data_pubblicazione" class="block text-gray-700 text-sm font-bold mb-2">Data e Ora di Pubblicazione:</label>
                        <input type="datetime-local" name="data_pubblicazione" id="data_pubblicazione" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    {{-- Campo Note --}}
                    <div class="mb-4">
                        <label for="note" class="block text-gray-700 text-sm font-bold mb-2">Inserisci delle note:</label>
                        <textarea name="note" id="note" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                    </div>

                    {{-- Pulsanti Azioni --}}
                    <div class="flex items-center justify-between">
                        <button type="submit" name="azione" value="bozza" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Salva come Bozza
                        </button>
                        <button type="submit" name="azione" value="invia" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Invia a {{ $cliente->nome }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modale Nextcloud --}}
    <div id="modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg w-[90%] max-w-7xl p-6 max-h-[90vh] overflow-y-auto">
            {{-- Header del modale --}}
            <div class="flex justify-between items-center border-b pb-3">
                <h2 class="text-xl font-bold text-gray-800">Seleziona File</h2>
                <button id="close-modal" class="text-gray-500 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" 
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" 
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Contenuto del modale --}}
            <div id="modal-content" class="mt-4 overflow-x-auto">
                <div class="grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] gap-4">
                    <p class="text-gray-500">Caricamento in corso...</p>
                </div>
            </div>

            {{-- Pulsante Conferma Selezione --}}
            <div class="flex justify-end mt-4">
                <button id="confirm-selection" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Conferma Selezione
                </button>
            </div>
        </div>
    </div>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</x-app-layout>
