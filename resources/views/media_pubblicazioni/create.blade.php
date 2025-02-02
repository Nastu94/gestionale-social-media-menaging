@if(auth()->user()->ruolo->nome !== 'Fotografo' && auth()->user()->ruolo->nome !== 'Amministratore')
    <script>
        window.location = "/";
    </script>
@endif
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Aggiungi Media per') }}: {{ $cliente->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900">                
                <div class="flex justify-center items-center mb-6">
                    <a href="{{ route('media_pubblicazioni.index', $cliente->id) }}" class="flex items-center block bg-white border border-gray-200 rounded-lg shadow p-4">
                        <div class="mr-4 p-3">
                            <img src="/logoClienti/{{ $cliente->logo_cliente }}" alt="Logo Cliente" class="w-16 h-16 object-cover rounded-full">
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold">{{ ucwords($cliente->nome) }}</h3>
                        </div>
                    </a>
                </div>
                <form action="{{ route('media_pubblicazioni.store', $cliente->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <x-input-label for="file" :value="__('File')" />
                        <x-file-input id="file" name="file" required />
                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button class="ml-4">
                            {{ __('Salva') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
