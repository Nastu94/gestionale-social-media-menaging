<x-app-layout>
    <div
        x-data='{
            isOpen: false,
            selectedPubId: null,
            selectedPubNote: "",
        }'
        x-on:publication-selected.document="
            selectedPubId   = $event.detail.id;
            selectedPubNote = $event.detail.note;
        "
        class="relative min-h-screen overflow-x-hidden bg-gray-50"
    >
        @if(auth()->user()->ruolo->nome !== 'Fotografo')
            <x-slot name="header">
                <div class="flex justify-between mb-4">
                    <!-- Titolo della dashboard -->
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Pubblicazioni') }}
                    </h2>

                    <!-- Pulsante per una nuova pubblicazione -->
                    <a id="newPublicationButton" 
                       href="#" 
                       class="mt-4 hidden inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                       Nuova Pubblicazione
                    </a>
                </div>
            </x-slot>

            <!-- LAYER PRINCIPALE (contenuto a due colonne) -->
            <!-- Aggiungiamo un transition su margin-right per effetto "push" -->
            <div 
                class="transition-all duration-300"
                :class="isOpen ? 'mr-64' : ''"  {{-- Se isOpen, aggiunge un margin-right di 16rem per fare spazio alla chat --}}
            >
                <div class="py-12">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        
                        <!-- Griglia a due colonne: sinistra (calendario/lista), destra (dettaglio) -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- COLONNA DI SINISTRA -->
                            <div>
                                <!-- Seleziona Cliente -->
                                <div class="bg-white shadow-xl sm:rounded-lg p-6 mb-4">
                                    <label for="client-select" class="block text-sm font-medium text-gray-700 mb-2">
                                        Seleziona Cliente:
                                    </label>
                                    <select id="client-select" 
                                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500" 
                                            name="cliente"
                                    >
                                        <option value="">Seleziona un cliente</option>
                                        @foreach($clienti as $cliente)
                                            <option value="{{ $cliente->id }}" data-token="{{ $cliente->token }}">{{ $cliente->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Pulsanti Asset e Media, se necessari -->
                                <div id="buttons-container" class="bg-white shadow-xl sm:rounded-lg p-6 mb-4 hidden">

                                    @if(auth()->user()->ruolo->nome === 'Amministratore')
                                        <a href="#" id="manageAssetsButton" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Gestisci Asset
                                        </a>
                                    @endif

                                    <!-- Aggiungiamo la sezione per il link guest/{token} -->
                                    <div class="mt-4">
                                        <label for="sharing-url" class="block text-sm font-medium text-gray-700 mb-1">
                                            Link da condividere con il cliente
                                        </label>
                                        <div class="flex space-x-2">
                                            <!-- Campo di testo per visualizzare il link -->
                                            <input 
                                                id="sharing-url" 
                                                type="text" 
                                                readonly 
                                                class="bg-gray-100 border border-gray-300 rounded-l-md shadow-sm p-2 flex-grow" 
                                                value=""
                                            />
                                            <!-- Pulsante per copiare il link -->
                                            <button 
                                                type="button" 
                                                id="copyLinkButton" 
                                                class="bg-red-500 hover:bg-red-600 rounded-r-md shadow-sm p-2 text-white"
                                            >
                                                Copia
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Calendario -->
                                <div id="calendar-container" class="bg-white shadow-xl sm:rounded-lg p-6 mb-4">
                                    <div class="overflow-x-auto">
                                        <div id="calendar" class="min-w-[500px]"></div>
                                    </div>
                                </div>

                                <!-- Carosello pubblicazioni -->
                                <div id="pubblicazioni-carousel" class="bg-white shadow-xl sm:rounded-lg p-6">
                                    <div class="flex items-center">
                                        <!-- freccia sinistra -->
                                        <button id="prev-slide" class="flex items-center justify-center bg-gray-300 text-gray-600 rounded-full w-10 h-10 shadow-lg mr-4">
                                            <x-heroicon-o-chevron-left class="w-6 h-6" />
                                        </button>

                                        <div id="pubblicazioni-container" class="flex-1 overflow-hidden">
                                            <div id="pubblicazioni-ul" class="flex transition-transform duration-300">
                                                @foreach($pubblicazioniChunks as $chunk)
                                                    <div class="w-full flex-shrink-0">
                                                        <ul class="grid grid-cols-1 gap-3 mt-3">
                                                            @foreach($chunk as $pubblicazione)
                                                                <li data-id="{{ $pubblicazione->id }}" 
                                                                    class="bg-gray-100 p-3 rounded-lg shadow-md flex items-center space-x-4 w-3/4 mx-auto cursor-pointer"
                                                                    onclick="loadPublicationDetails({{ $pubblicazione->id }})"
                                                                >
                                                                <div class="w-16 h-16 bg-gray-300 flex-shrink-0 rounded overflow-hidden">
                                                                    @if($pubblicazione->media->first())
                                                                        @php
                                                                            // Rimuovi la parte iniziale dell'URL di Nextcloud

                                                                            $relativePath = str_replace(
                                                                                'parte-iniziale-dell-URL-nextcloud',
                                                                                '',
                                                                                $pubblicazione->media->first()->nome
                                                                            );
                                                                        @endphp

                                                                        <img 
                                                                            src="{{ route('file.show', ['path' => $relativePath]) }}" 
                                                                            alt="Media Pubblicazione" 
                                                                            class="w-full h-full object-cover"
                                                                        >
                                                                    @else
                                                                        <span class="text-gray-500 text-sm">Nessun media</span>
                                                                    @endif
                                                                </div>

                                                                    <div class="flex-1 min-w-0">
                                                                        <p class="text-sm text-gray-600">
                                                                            {{ $pubblicazione->data_pubblicazione }} - 
                                                                            <b>{{ $pubblicazione->cliente->nome }}</b>
                                                                        </p>
                                                                        <p class="text-md font-semibold text-gray-900 truncate">
                                                                            {{ Str::limit($pubblicazione->testo, 50) }}
                                                                        </p>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- freccia destra -->
                                        <button id="next-slide" class="flex items-center justify-center bg-gray-300 text-gray-600 rounded-full w-10 h-10 shadow-lg ml-4">
                                            <x-heroicon-o-chevron-right class="w-6 h-6" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- COLONNA DI DESTRA -->
                            <div>
                                <!-- Dettaglio Pubblicazione -->
                                <div id="dettaglio-pubblicazione" class="bg-white shadow-xl sm:rounded-lg p-6 text-gray-900 mb-4">
                                    <h3 class="font-semibold text-lg text-gray-700">Dettaglio Pubblicazione</h3>
                                    <div id="pubblicazione-dettaglio-contenuto">
                                        <!-- Dettaglio popolato tramite AJAX -->
                                    </div>
                                </div>

                                <!-- Chat della pubblicazione (se serve) -->
                                <div id="pubblicazione-chat" class="bg-white shadow-xl sm:rounded-lg p-6 text-gray-900 hidden">
                                    <h3 class="font-semibold text-lg text-gray-700">Chat</h3>
                                    <div id="comment-section" class="max-h-60 overflow-y-auto border p-3 rounded bg-gray-50">
                                        <!-- Commenti caricati dinamicamente -->
                                    </div>
                                    <form id="comment-form" class="mt-4" method="POST">
                                        @csrf
                                        <!-- Campo nascosto ID pubblicazione -->
                                        <input type="hidden" id="publication-id" name="publication_id" value="">
                                        <textarea id="comment-text" name="commento" class="w-full p-2 border rounded focus:outline-none focus:shadow-outline" placeholder="Scrivi un commento..." required></textarea>
                                        <button type="submit" class="mt-2 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                            Invia
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div> <!-- Fine grid 2 colonne -->

                    </div> <!-- Fine container -->
                </div> <!-- Fine py-12 -->

                <!-- Modale Nextcloud -->
                <div id="modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-lg w-[90%] max-w-7xl p-6 max-h-[90vh] overflow-y-auto">
                        <!-- Header del modale -->
                        <div class="flex justify-between items-center border-b pb-3">
                            <h2 class="text-xl font-bold text-gray-800">Seleziona File</h2>
                            <button id="close-modal" class="text-gray-500 hover:text-gray-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Contenuto del modale -->
                        <div id="modal-content" class="mt-4 overflow-x-auto">
                            <div class="grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] gap-4">
                                <p class="text-gray-500">Caricamento in corso...</p>
                            </div>
                        </div>
                        <!-- Pulsante Conferma Selezione -->
                        <div class="flex justify-end mt-4">
                            <button id="confirm-selection" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Conferma Selezione
                            </button>
                        </div>
                    </div>
                </div>

                @vite(['resources/css/app.css', 'resources/js/app.js'])

                @php
                    $calendarEvents = $pubblicazioniCalendario->map(function ($pubblicazione) {
                        return [
                            'title' => '',
                            'start' => \Carbon\Carbon::parse($pubblicazione->data_pubblicazione)->format('Y-m-d\TH:i:s'),
                            'classNames' => 'stato-' . $pubblicazione->stato_id
                        ];
                    })->toArray();
                @endphp

                <script>
                    // Passaggio variabili a JavaScript
                    window.calendarEvents = @json($calendarEvents);
                    window.clientSelectId = 'client-select';
                    window.pubContainerId = 'pubblicazioni-ul';
                    window.pubDetailsContainerId = 'pubblicazione-dettaglio-contenuto';
                    window.newPublicationButtonId = 'newPublicationButton';
                    window.buttonsContainerId = 'buttons-container';
                    window.manageAssetsButtonId = 'manageAssetsButton';
                    window.viewMediaButtonId = 'viewMediaButton';
                    window.prevSlideButtonId = 'prev-slide';
                    window.nextSlideButtonId = 'next-slide';
                </script>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const clientSelect = document.getElementById('client-select');
                        const sharingUrl   = document.getElementById('sharing-url');
                        const copyButton   = document.getElementById('copyLinkButton');

                        // Quando cambia il valore del select
                        clientSelect.addEventListener('change', function() {
                            const selectedOption = clientSelect.options[clientSelect.selectedIndex];
                            const token = selectedOption.getAttribute('data-token');

                            if (token) {
                                // Aggiorna il campo con l'URL relativo a quel token
                                sharingUrl.value = `${window.location.origin}/guest/${token}`;
                            } else {
                                sharingUrl.value = '';
                            }
                        });

                        // Logica per copiare
                        if (copyButton && sharingUrl) {
                            copyButton.addEventListener('click', function() {
                                sharingUrl.select();
                                document.execCommand('copy');
                                alert('Link copiato negli appunti!');
                            });
                        }
                    });
                </script>
            </div> <!-- Fine LAYER PRINCIPALE -->

            <!-- LAYER SIDEBAR CHAT -->
            <div 
                class="absolute top-0 right-0 w-64 h-full bg-white shadow-md transition-transform duration-300 z-50"
                :class="isOpen ? 'translate-x-0' : 'translate-x-full'"
            >
                <div class="flex items-center justify-between p-4 bg-gray-200">
                    <h2 class="text-lg font-semibold">Ssody GPT</h2>
                    <button class="text-gray-600 hover:text-gray-900" @click="isOpen = false">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-4 space-y-2">
                    <!-- Avviso se nessuna pubblicazione -->
                    <template x-if="!selectedPubId">
                        <p class="text-red-600 font-semibold">
                            Seleziona prima una pubblicazione per generare il testo con GPT.
                        </p>
                    </template>

                    <!-- Se c'è una pubblicazione selezionata -->
                    <template x-if="selectedPubId">
                        <div>
                            <p class="text-gray-700 mb-2">
                                Pubblicazione ID: <span x-text="selectedPubId"></span>
                            </p>

                            <!-- Form che chiama la funzione globale in app.js -->
                            <form @submit.prevent="window.generateGPT(selectedPubId, selectedPubNote)">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Note della Pubblicazione</label>
                                <textarea 
                                    x-model="selectedPubNote"
                                    class="border rounded p-2 w-full mb-2 focus:outline-none"
                                    rows="4"
                                ></textarea>
                                <button 
                                    type="submit"
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Genera testo GPT
                                </button>
                            </form>

                            <div id="gpt-output" class="mt-4 text-gray-700 whitespace-pre-wrap">
                                <!-- Qui apparirà il testo generato -->
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- PULSANTE FISSO PER APRIRE LA CHAT GPT (lato destro) -->
            <button 
                class="fixed top-1/2 right-0 transform -translate-y-1/2 bg-red-600 text-white p-3 rounded-l shadow-md flex items-center"
                @click="isOpen = !isOpen"
            >
                <!-- Icona Chat -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2h-2M7 8H5a2 2 0 00-2 2v8a2 2 0 002 2h2"/>
                </svg>
                Ssody
            </button>
        @else
            <script> window.location = "/"; </script>
        @endif


    </div><!-- fine x-data -->
</x-app-layout>
