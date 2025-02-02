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
            {{ __('Media di') }}: {{ $cliente->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900 rounded-lg">
                <!-- Header con logo e nome del cliente -->
                <div class="flex justify-between items-center mb-6">
                    @php
                        if(auth()->user()->ruolo->nome === 'Fotografo')
                            $href = route('clienti.index');
                        else
                            $href = route('clienti.show', $cliente->id)
                    @endphp
                    <a href="{{ $href }}" class="flex items-center block bg-white border border-gray-200 rounded-lg shadow p-4">
                        <div class="mr-4 p-3">
                            <img src="/logoClienti/{{ $cliente->logo_cliente }}" alt="Logo Cliente" class="w-16 h-16 object-cover rounded-full">
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold">{{ ucwords($cliente->nome) }}</h3>
                        </div>
                    </a>
                    <!-- Sezione pulsante aggiungi media visibile solo ai fotografi -->
                    @if(auth()->user()->ruolo->nome === 'Fotografo' || auth()->user()->ruolo->nome === 'Amministratore')
                        <div class="flex justify-center mb-4">
                            <a href="{{ route('media_pubblicazioni.create', $cliente->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Aggiungi Media
                            </a>
                        </div>
                    @endif
                </div>

                @if(auth()->user()->ruolo->nome !== 'Fotografo')
                    <!-- Pulsante Selezione Multipla -->
                    <button id="toggleSelection" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Selezione Multipla
                    </button>
                @endif

                <!-- Griglia per i media -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                    @foreach($media as $mediaItem)
                        <div class="relative block media-item">
                            <a href="{{ route('media_pubblicazioni.show', $mediaItem->id) }}" class="media-link">
                                <div class="bg-white border border-gray-200 rounded-lg shadow p-4 flex items-center justify-center hover:bg-gray-100 transition ease-in-out duration-150" style="position: relative; height: 339px;">
                                    @if(preg_match('/\.(jpeg|png|jpg|gif)$/i', $mediaItem->nome))
                                        <img src="{{ $mediaItem->nome }}" alt="Media" class="object-cover w-full h-full">
                                    @elseif(preg_match('/\.(mp4|mov|avi|wmv)$/i', $mediaItem->nome))
                                        <video controls class="object-cover w-full h-full">
                                            <source src="{{ $mediaItem->nome }}" type="video/mp4">
                                            Il tuo browser non supporta il tag video.
                                        </video>
                                    @else
                                        File non supportato
                                    @endif
                                    <input type="checkbox" class="hidden absolute top-3 right-3" name="selected_media[]" value="{{ $mediaItem->id }}">
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <!-- Pulsante Pubblica -->
                <form id="publishForm" action="{{ route('pubblicazioni.create', $cliente->id) }}" method="GET" class="mt-4 hidden">
                    <input type="hidden" id="selectedMediaInput" name="media" value="">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Pubblica
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleSelectionButton = document.getElementById('toggleSelection');
            const publishForm = document.getElementById('publishForm');
            const selectedMediaInput = document.getElementById('selectedMediaInput');
            const mediaItems = document.querySelectorAll('.media-item');
            const mediaLinks = document.querySelectorAll('.media-link');

            toggleSelectionButton.addEventListener('click', function () {
                const isSelecting = this.textContent.includes('Annulla');
                this.textContent = isSelecting ? 'Selezione Multipla' : 'Annulla Selezione';
                publishForm.classList.toggle('hidden', isSelecting);
                mediaItems.forEach(item => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (isSelecting) {
                        checkbox.classList.add('hidden');
                        checkbox.checked = false;
                        item.classList.remove('border-blue-500');
                    } else {
                        checkbox.classList.remove('hidden');
                    }
                });
                mediaLinks.forEach(link => {
                    link.classList.toggle('pointer-events-none', !isSelecting);
                });
            });

            mediaItems.forEach(item => {
                item.addEventListener('click', function () {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        this.classList.toggle('border-blue-500', checkbox.checked);
                        const selectedMedia = document.querySelectorAll('input[type="checkbox"]:checked');
                        const selectedMediaIds = Array.from(selectedMedia).map(checkbox => checkbox.value);
                        selectedMediaInput.value = selectedMediaIds.join(',');
                        publishForm.classList.toggle('hidden', selectedMediaIds.length === 0);
                    }
                });
            });
        });
    </script>
</x-app-layout>
