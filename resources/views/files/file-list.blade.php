<div>
    <!-- Pulsante Indietro se c'è parentPath -->
    @if (isset($parentPath) && $parentPath !== null)
        <div class="flex items-center justify-between bg-gray-100 p-2 border-b">
            <button id="go-back"
                    class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded"
                    data-path="{{ $parentPath }}">
                ← Indietro
            </button>
        </div>
    @endif
    
    <!-- Contenitore principale con scroll verticale -->
    <div id="file-container" class="overflow-y-auto h-[400px] bg-white p-4">
        <div>
            <!-- Grid a 3 colonne, con spaziatura -->
            <ul class="grid grid-cols-3 gap-6">
                @forelse ($files as $file)
                    <li class="p-4 border border-gray-300 rounded bg-gray-50 flex flex-col items-center justify-center">
                        @if ($file['type'] === 'folder')
                            <button class="select-file text-blue-500 hover:underline folder"
                                    data-path="{{ $file['path'] }}">
                                📁
                                <span class="mt-2 truncate">{{ $file['name'] }}</span>
                            </button>
                        @else
                            @php
                                $relativePath = ltrim($file['path'], '/');
                            @endphp

                            <!-- Immagine -->
                            <img src="{{ route('file.show', ['path' => $relativePath]) }}"
                                 alt="File"
                                 class="w-24 h-24 object-cover rounded mb-2 transform transition-transform duration-200 hover:scale-110 active:scale-[1.5] cursor-pointer">
                            
                            <!-- Bottone per selezionare il file, con data-path che punta alla route interna -->
                            <button class="select-file text-green-500 hover:underline file"
                                    data-path="{{ route('file.show', ['path' => $relativePath]) }}">
                                <span class="mt-2 truncate">{{ $file['name'] }}</span>
                            </button>
                        @endif
                    </li>
                @empty
                    <li>Nessun file trovato.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Footer del modale: caricamento file -->
    <div class="flex items-center justify-between border-t mt-4 pt-4">
        <!-- Campo file -->
        <input 
            type="file" 
            id="fileToUpload" 
            accept="image/*,video/*"
            class="border rounded p-2"
            multiple
        />
        <!-- Pulsante Carica -->
        <button 
            type="button" 
            id="uploadFileButton"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
            Carica su Nextcloud
        </button>
    </div>
</div>
