<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Esplora File') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <!-- Barra di navigazione -->
                <div class="p-4 bg-gray-100 border-b">
                    <button id="go-back" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded">
                        ← Indietro
                    </button>
                </div>

                <!-- Contenuto principale -->
                <div id="file-container" class="p-4 grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] gap-4">
                    @include('files.file-list', ['files' => $files])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
