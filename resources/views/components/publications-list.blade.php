<ul class="grid grid-cols-1 gap-4 mt-3">
    @foreach($pubblicazioniLista as $pubblicazione)
        <li class="bg-gray-100 p-4 rounded-lg shadow-md flex items-center space-x-4 cursor-pointer" 
            data-id="{{ $pubblicazione->id }}" 
            onclick="loadPublicationDetails({{ $pubblicazione->id }})">
            <div class="w-20 h-20 bg-gray-300 flex-shrink-0 rounded overflow-hidden">
                @if($pubblicazione->media->first())
                    <img src="{{ $pubblicazione->media->first()->nome }}" 
                         alt="Media Pubblicazione" class="w-full h-full object-cover">
                @else
                    <span class="text-gray-500 text-sm">Nessun media</span>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-600">{{ $pubblicazione->data_pubblicazione }} - 
                   <b>{{ optional($pubblicazione->media->first()->cliente)->nome }}</b></p>
                <p class="text-lg font-semibold text-gray-900 truncate">{{ Str::limit($pubblicazione->testo, 50) }}</p>
            </div>
        </li>
    @endforeach
</ul>
