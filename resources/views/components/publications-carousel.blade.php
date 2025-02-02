@foreach($pubblicazioniChunks as $chunk)
    <div class="w-full flex-shrink-0">
        <ul class="grid grid-cols-1 gap-3 mt-3">
            @foreach($chunk as $pubblicazione)
                <li data-id="{{ $pubblicazione->id }}" class="bg-gray-100 p-3 rounded-lg shadow-md flex items-center space-x-4 w-3/4 mx-auto cursor-pointer"
                    onclick="loadPublicationDetails({{ $pubblicazione->id }})">
                    <div class="w-16 h-16 bg-gray-300 flex-shrink-0 rounded overflow-hidden">
                        @if($pubblicazione->media->first())
                            @php
                                // Rimuovi la parte iniziale dell'URL di Nextcloud

                                $relativePath = str_replace(
                                    'URL-nextcloud-DAV',
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
                        <p class="text-sm text-gray-600">{{ $pubblicazione->data_pubblicazione }} - <b>{{ optional($pubblicazione->media->first()->cliente)->nome }}</b></p>
                        <p class="text-md font-semibold text-gray-900 truncate">{{ Str::limit($pubblicazione->testo, 50) }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endforeach
