<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modifica Pubblicazione') }}
        </h2>
    </x-slot>
    
    <div class="py-12 flex">
        <!-- Colonna sinistra -->
        <div class="w-4/6 pr-3">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900">
                    <div class="flex justify-center items-center mb-6">
                        <a href="{{ route('pubblicazioni.show', $pubblicazione->id) }}" class="flex items-center block bg-white border border-gray-200 rounded-lg shadow p-4">
                            <div class="mr-4 p-3">
                                <img src="/logoClienti/{{ $pubblicazione->cliente->logo_cliente }}" alt="Logo Cliente" class="w-16 h-16 object-cover rounded-full">
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold">{{ ucwords($pubblicazione->cliente->nome) }}</h3>
                                <p class="font-semibold">{{ ucwords($pubblicazione->stato->nome_stato) }}</p>
                            </div>
                        </a>
                    </div>
                    <form method="POST" action="{{ route('pubblicazioni.update', $pubblicazione) }}">
                        @csrf
                        @method('PUT')

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

                        <div class="mb-4">
                            <x-input-label for="testo" :value="__('Testo')" />
                            <textarea name="testo" id="testo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline h-32 resize-none" {{ auth()->user()->ruolo->nome === 'Cliente' ? 'readonly' : '' }}>{{ $pubblicazione->testo }}</textarea>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="data_pubblicazione" :value="__('Data di Pubblicazione')" />
                            <input type="datetime-local" name="data_pubblicazione" id="data_pubblicazione" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ $pubblicazione->data_pubblicazione }}" {{ auth()->user()->ruolo->nome === 'Cliente' ? 'readonly' : '' }}>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            @if(auth()->user()->ruolo->nome === 'Cliente' && $pubblicazione->stato->id == 3)
                                <button type="submit" name="azione" value="approva" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">
                                    Approva
                                </button>
                            @elseif(auth()->user()->ruolo->nome === 'Dipendente' || auth()->user()->ruolo->nome === 'Amministratore')
                                @if($pubblicazione->stato->id == 1 || $pubblicazione->stato->id == 2)
                                    <button type="submit" name="azione" value="invia_al_cliente" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                        Invia al Cliente
                                    </button>
                                @endif
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Colonna Chat -->
        <div class="w-2/6 pl-3">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900" x-data="commentSection">
                    <div class="flex flex-col space-y-4" id="comment-section">
                        @foreach ($pubblicazione->commenti->sortBy('data_testo') as $commento)
                            @php
                                $isAdmin = auth()->user()->ruolo->nome === 'Amministratore';
                                $isDipendente = auth()->user()->ruolo->nome === 'Dipendente';
                                $isUserComment = ($commento->utente === 'AmicoWeb' && ($isAdmin || $isDipendente)) || ($commento->utente === 'Cliente' && !$isAdmin && !$isDipendente);
                                $textAlignment = $isUserComment ? 'text-right' : 'text-left';
                                $bgColor = $isUserComment ? 'bg-green-100' : 'bg-white';
                                $nomeCliente = $isUserComment ? $commento->utente : $pubblicazione->cliente->nome;
                            @endphp
                            <div class="{{ $textAlignment }} {{ $bgColor }} p-2 rounded" id="comment-{{ $commento->id }}">
                                <p><strong>{{ $nomeCliente }}:</strong> {{ $commento->commento }}</p>
                                <div class="text-xs text-gray-600">{{ $commento->data_testo }}</div>
                            </div>
                        @endforeach
                    </div>
                    @if($pubblicazione->stato->id < 4)
                    <form class="p-2" @submit.prevent="submitComment" action="{{ route('commenti.store', $pubblicazione->id) }}" method="POST">
                        @csrf
                        <textarea name="commento" class="w-full p-2 border rounded focus:outline-none focus:shadow-outline" placeholder="Scrivi un commento..." required></textarea>
                        <button type="submit" class="mt-2 w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Invia
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('commentSection', () => ({
                submitComment(event) {
                    const form = event.target;
                    const commentTextArea = form.querySelector('textarea[name="commento"]');
                    const commentText = commentTextArea.value;
                    if (commentText.trim() === "") return; // Assicurati che il commento non sia vuoto

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            commento: commentText
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const commentSection = document.getElementById('comment-section');
                            const newComment = document.createElement('div');
                            const isUserComment = data.utente === 'Cliente';
                            const textAlignment = 'text-right';
                            const bgColor = 'bg-green-100';
                            const formattedDate = new Date(data.commento.data_testo).toLocaleString('it-IT', {
                                day: '2-digit', month: '2-digit', year: 'numeric',
                                hour: '2-digit', minute: '2-digit', second: '2-digit'
                            });

                            newComment.className = `${textAlignment} ${bgColor} p-2 rounded`;
                            newComment.innerHTML = `
                                <p><strong>${data.nome_cliente}:</strong> ${data.commento.commento}</p>
                                <div class="text-xs text-gray-600">${formattedDate}</div>
                            `;

                            commentSection.appendChild(newComment);
                            commentTextArea.value = ''; // Resetta il campo di testo
                        }
                    })
                    .catch(error => {
                        console.error('Errore durante l\'invio del commento:', error);
                    });
                }
            }));
        });
    </script>

</x-app-layout>