<x-guest-layout>
    <div 
        x-data="{ 
            open: false,
            pubblicazioneId: null, 
            motivazione: '' 
        }" 
        x-on:open-rejection-modal.window="
            open = true; 
            pubblicazioneId = $event.detail.id; 
        "
        x-cloak
    >
        {{-- Messaggi di Successo ed Errori --}}
        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Titolo --}}
        <h1 class="text-2xl font-bold mb-4 text-center">Pubblicazioni in Valutazione</h1>

        {{-- Lista delle Pubblicazioni --}}
        @if($pubblicazioni->isEmpty())
            <p class="text-gray-500 text-center">Nessuna nuova pubblicazione in attesa di valutazione è stata trovata.</p>
        @else        
            <p class="text-center text-gray-600 mb-6">
                Pubblicazioni per <span class="font-semibold">{{ $cliente->nome }}</span> in fase di valutazione.
            </p>
            <ul class="space-y-4">
                @foreach($pubblicazioni as $pubblicazione)
                    <li class="bg-gray-50 p-4 rounded shadow-md">
                        {{-- Carosello dei media --}}
                        @if($pubblicazione->media->count() > 0)
                            <div 
                                x-data="{ currentIndex: 0, media: {{ $pubblicazione->media->toJson() }} }" 
                                class="relative w-full h-80">
                                
                                <template x-for="(mediaItem, index) in media" :key="index">
                                    <div 
                                        x-show="currentIndex === index"
                                        x-transition:enter="transition ease-out duration-500"
                                        x-transition:enter-start="opacity-0 scale-90"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-90"
                                        class="absolute inset-0 flex items-center justify-center">
                                        
                                        <template x-if="mediaItem.nome.match(/\.(jpeg|png|jpg|gif)$/i)">
                                            <img :src="`{{ route('file.show', '') }}/${mediaItem.nome}`" alt="Media della Pubblicazione" class="object-contain h-full w-full rounded-lg">
                                        </template>
                                        <template x-if="mediaItem.nome.match(/\.(mp4|mov|avi|wmv)$/i)">
                                            <video controls class="object-contain h-full w-full rounded-lg">
                                                <source :src="`{{ route('file.show', '') }}/${mediaItem.nome}`" type="video/mp4">
                                                Il tuo browser non supporta il tag video.
                                            </video>
                                        </template>
                                    </div>
                                </template>
                                
                                {{-- Pulsanti navigazione carosello --}}
                                <button 
                                    type="button"
                                    @click="currentIndex = (currentIndex > 0) ? currentIndex - 1 : media.length - 1"
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-500 hover:bg-gray-600 text-white px-2 py-1 rounded-l">
                                    &#10094;
                                </button>
                                <button 
                                    type="button"
                                    @click="currentIndex = (currentIndex < media.length - 1) ? currentIndex + 1 : 0"
                                    class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-500 hover:bg-gray-600 text-white px-2 py-1 rounded-r">
                                    &#10095;
                                </button>

                                {{-- Indicatore posizione media --}}
                                <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 bg-white text-black text-xs px-2 py-1 rounded shadow">
                                    <span x-text="(currentIndex + 1) + ' / ' + media.length"></span>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 mb-4 text-center">Nessun media associato a questa pubblicazione.</p>
                        @endif

                        {{-- Testo e data sotto il carosello --}}
                        <div class="mt-6 text-center">
                            <p class="text-lg font-semibold text-gray-800">{{ $pubblicazione->testo }}</p>
                            <p class="text-gray-600 mt-2">{{ \Carbon\Carbon::parse($pubblicazione->data_pubblicazione)->format('d-m-Y H:i') }}</p>
                        </div>

                        {{-- Pulsanti Accetta e Rifiuta --}}
                        <div class="mt-6 flex justify-center space-x-4">
                            <form method="POST" action="{{ route('guest.accept', $pubblicazione->id) }}">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    Accetta
                                </button>
                            </form>
                            <button 
                                @click="$dispatch('open-rejection-modal', { id: {{ $pubblicazione->id }} })"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Rifiuta
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Modale per il Rifiuto --}}
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white w-full max-w-lg p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold mb-4">Motivazione del Rifiuto</h2>
                <form method="POST" :action="`/guest/reject/${pubblicazioneId}`">
                    @csrf
                    <textarea 
                        x-model="motivazione" 
                        name="motivazione" 
                        class="w-full p-3 border rounded-lg focus:outline-none focus:ring focus:ring-red-500"
                        placeholder="Inserisci la motivazione del rifiuto..." 
                        required
                    ></textarea>
                    <div class="flex justify-end mt-4">
                        <button type="button" @click="open = false" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                            Annulla
                        </button>
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Conferma Rifiuto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
