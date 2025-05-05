@php
    use Illuminate\Support\Str;

    // Prefisso definito in config/services.php → .env
    $baseUri = rtrim(config('services.nextcloud.base_uri'), '/').'/';

    // Mappiamo la collezione di media, convertendo i link Nextcloud in link "interni"
    $mediaMappati = $pubblicazione->media->map(function ($m) use ($baseUri) {

        // Togli il prefisso Nextcloud e ottieni il percorso relativo
        $relativePath = Str::after($m->nome, $baseUri);

        // Rotta interna che serve il file
        $m->nome = route('file.show', ['path' => $relativePath]);

        return $m;
    });
@endphp

<div class="bg-white shadow-md rounded-lg p-6" id="publication-details-container"
     data-pub-id="{{ $pubblicazione->id }}"
     data-pub-note="{{ $pubblicazione->note }}"
>
    <h2 class="text-xl font-bold text-gray-800 mb-4">{{ $pubblicazione->cliente->nome }}</h2>

    <!-- Carosello con Alpine -->
    <div x-data="{ 
            currentIndex: 0, 
            media: {{ $mediaMappati->toJson() }},    // <-- Usiamo la collezione mappata
            addFiles(files) {
                files.forEach(file => this.media.push({ nome: file }));
            },
            setFiles(files) {
                this.media = files.map(file => ({ nome: file }));
                this.currentIndex = 0;
            }
        }" 
        x-ref="carouselComponent" 
        class="relative mt-4 mb-4"
        x-show="media.length > 0">

        <template x-for="(mediaItem, index) in media" :key="index">
            <div x-show="currentIndex === index" class="w-full h-80 flex items-center justify-center">
                <template x-if="mediaItem.nome.match(/\.(jpeg|png|jpg|gif)$/i)">
                    <img :src="mediaItem.nome" alt="Media della Pubblicazione" class="object-contain h-full w-full rounded-lg">
                </template>
                <template x-if="mediaItem.nome.match(/\.(mp4|mov|avi|wmv)$/i)">
                    <video controls class="object-contain h-full w-full rounded-lg">
                        <source :src="mediaItem.nome" type="video/mp4">
                        Il tuo browser non supporta il tag video.
                    </video>
                </template>
            </div>
        </template>

        <button type="button" @click="currentIndex = (currentIndex > 0) ? currentIndex - 1 : media.length - 1" 
                class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-500 text-white px-2 py-1 rounded-l">
            &#10094;
        </button>
        <button type="button" @click="currentIndex = (currentIndex < media.length - 1) ? currentIndex + 1 : 0" 
                class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-500 text-white px-2 py-1 rounded-r">
            &#10095;
        </button>
    </div>

    <p id="publication-date" class="text-gray-600 mb-4">{{ $pubblicazione->data_pubblicazione }}</p>
    <p id="publication-text" class="text-gray-900">{{ $pubblicazione->testo }}</p>
    <br>
    <pre class="text-center text-gray-600">{{ $pubblicazione->cliente->firma ?? 'Firma non disponibile' }}</pre>

    <!-- Modalità Modifica -->
    <form id="publication-edit-form" class="hidden mt-4">
        @csrf
        @method('PUT')
        <input type="hidden" id="azione" name="azione" value="invia_al_cliente">

        <!-- Contenitore per input:hidden dei file selezionati in fase di modifica -->
        <div id="selected-files-container-edit"></div>

        <!-- Pulsante Sfoglia Nextcloud, nascosto finché non si va in modifica -->
        <div class="mb-4">
            <button type="button" id="browse-nextcloud" class="mt-2 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline hidden">
                Sfoglia Nextcloud
            </button>
        </div>

        <div class="mb-4">
            <label for="publication-edit-text" class="block text-sm font-medium text-gray-700">Testo</label>
            <textarea id="publication-edit-text" name="testo" rows="4" 
                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ $pubblicazione->testo }}</textarea>
        </div>
        <div class="mb-4">
            <label for="publication-edit-date" class="block text-sm font-medium text-gray-700">Data di Pubblicazione</label>
            <input type="datetime-local" id="publication-edit-date" name="data_pubblicazione" 
                   value="{{ $pubblicazione->data_pubblicazione }}" 
                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
        </div>

    </form>

    <div class="flex items-center space-x-4 mt-4">
        @if(in_array($pubblicazione->stato->id, [1, 2]))
            <button id="edit-button" 
                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Modifica
            </button>
            <button id="cancel-edit-button" 
                    class="hidden bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Termina Modifica
            </button>
            <button id="save-edit-button" 
                    class="hidden bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Salva Modifiche
            </button>
        @endif
    </div>
</div>
