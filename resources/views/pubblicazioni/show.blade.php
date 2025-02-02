<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dettagli Pubblicazione') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900">
                <div class="flex justify-center items-center mb-6">                    
                    @php
                        $href = auth()->user()->ruolo->nome === 'Cliente' ? route('dashboard') : route('pubblicazioni.index');
                    @endphp
                    <a href="{{ $href }}" class="flex items-center block bg-white border border-gray-200 rounded-lg shadow p-4">
                        <div class="mr-4 p-3">
                            <img src="/logoClienti/{{ $pubblicazione->media->first()->cliente->logo_cliente }}" alt="Logo Cliente" class="w-16 h-16 object-cover rounded-full">
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold">{{ ucwords($pubblicazione->media->first()->cliente->nome) }}</h3>
                        </div>
                    </a>
                </div>
                <div class="flex justify-between items-start mb-6">
                    <div class="w-1/2 pr-4">
                        <!-- Carosello dei Media -->
                        <div x-data="{ currentIndex: 0, media: {{ json_encode($pubblicazione->media) }} }" class="relative mb-4">
                            <template x-for="(mediaItem, index) in media" :key="mediaItem.id">
                                <div x-show="currentIndex === index" class="w-full h-80 flex items-center justify-center">
                                    <template x-if="mediaItem.nome.match(/\.(jpeg|png|jpg|gif)$/i)">
                                        <img :src="'/mediaPubblicazioni/' + mediaItem.nome" alt="Media della Pubblicazione" class="object-contain h-full w-full rounded-lg">
                                    </template>
                                    <template x-if="mediaItem.nome.match(/\.(mp4|mov|avi|wmv)$/i)">
                                        <video controls class="object-contain h-full w-full rounded-lg">
                                            <source :src="'/mediaPubblicazioni/' + mediaItem.nome" type="video/mp4">
                                            Il tuo browser non supporta il tag video.
                                        </video>
                                    </template>
                                </div>
                            </template>
                            <button type="button" @click="currentIndex = (currentIndex > 0) ? currentIndex - 1 : media.length - 1" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-500 text-white px-2 py-1 rounded-l">
                                &#10094;
                            </button>
                            <button type="button" @click="currentIndex = (currentIndex < media.length - 1) ? currentIndex + 1 : 0" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-500 text-white px-2 py-1 rounded-r">
                                &#10095;
                            </button>
                        </div>

                    </div>
                    <div class="w-1/2 pl-4">
                        <p class="mb-4">{{ $pubblicazione->testo }}</p>
                        <p class="mb-2"><strong>Data di pubblicazione:</strong> {{ $pubblicazione->data_pubblicazione }}</p>
                        <p class="mb-2"><strong>Stato:</strong> {{ $pubblicazione->stato ? $pubblicazione->stato->nome_stato : 'Non definito' }}</p>

                        @if(auth()->user()->ruolo->nome === 'Cliente')
                            @if($pubblicazione->stato_id !== 3)
                                <a href="{{ route('pubblicazioni.edit', $pubblicazione->id) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Mostra Modifiche
                                </a>
                            @else
                                <a href="{{ route('pubblicazioni.edit', $pubblicazione->id) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Modifica
                                </a>
                            @endif
                        @else
                            @if(in_array($pubblicazione->stato->id, [1, 2]))
                                <a href="{{ route('pubblicazioni.edit', $pubblicazione->id) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Modifica
                                </a>
                            @elseif($pubblicazione->stato->id == 3 || $pubblicazione->stato->id > 4)
                                <a href="{{ route('pubblicazioni.edit', $pubblicazione->id) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Mostra Modifiche
                                </a>
                            @elseif($pubblicazione->stato->id == 4)
                                <div class="flex space-x-4">
                                    <a href="{{ route('pubblicazioni.edit', $pubblicazione->id) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Mostra Modifiche
                                    </a>
                                    <a href="{{ route('pubblicazioni.pianifica', $pubblicazione->id) }}" class="inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Pianifica
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>