@if(auth()->user()->ruolo->nome !== 'Amministratore')
    <script>
        window.location = "/";
    </script>
@endif

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Aggiungi Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900">
                <form id="create-client-form" action="{{ route('clienti.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NOME -->
                        <div>
                            <x-input-label for="nome" :value="__('Nome')" />
                            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome" :value="old('nome')" required autofocus />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>

                        <!-- UTENTE CLIENTE -->
                        <div>
                            <x-input-label for="id_utente_cliente" :value="__('Cliente')" />
                            <x-select-input name="id_utente_cliente" id="id_utente_cliente" :options="$clienti->pluck('name', 'id')" required />
                            <x-input-error :messages="$errors->get('id_utente_cliente')" class="mt-2" />
                        </div>

                        <!-- PACCHETTO -->
                        <div>
                            <x-input-label for="pacchetto_id" :value="__('Pacchetto')" />
                            <x-select-input name="pacchetto_id" id="pacchetto_id" :options="$pacchetti->pluck('nome', 'id')" required />
                            <x-input-error :messages="$errors->get('pacchetto_id')" class="mt-2" />
                        </div>

                        <!-- PERMESSI (Dipendenti e Fotografi) -->
                        <div>
                            <x-input-label for="id_utenti" :value="__('Dipendenti e Fotografi')" />
                            @foreach ($utenti as $utente)
                                <x-checkbox-input name="id_utenti" id="id_utenti_{{ $utente->id }}" :value="$utente->id" :label="$utente->name" />
                            @endforeach
                            <x-input-error :messages="$errors->get('id_utenti')" class="mt-2" />
                        </div>

                        <!-- SITO WEB -->
                        <div>
                            <x-input-label for="sito_web" :value="__('Sito Web')" />
                            <x-select-input name="sito_web" id="sito_web" :options="['0' => 'No', '1' => 'Sì']" required />
                            <x-input-error :messages="$errors->get('sito_web')" class="mt-2" />
                        </div>

                        <!-- LOGO CLIENTE -->
                        <div>
                            <x-input-label for="logo_cliente" :value="__('Logo Cliente')" />
                            <x-file-input id="logo_cliente" name="logo_cliente" />
                            <x-input-error :messages="$errors->get('logo_cliente')" class="mt-2" />
                        </div>

                        <!-- CAMPO CELLULARE -->
                        <div>
                            <x-input-label for="cellulare" :value="__('Cellulare')" />
                            <x-text-input id="cellulare" class="block mt-1 w-full" type="number" name="cellulare" />
                            <x-input-error :messages="$errors->get('cellulare')" class="mt-2" />
                        </div>
                    </div>

                    <!-- FIRMA CLIENTE -->
                    <div>
                        <x-input-label for="firma" :value="__('Firma')" />
                        <textarea id="firma" name="firma" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="4">{{ old('firma') }}</textarea>
                        <x-input-error :messages="$errors->get('firma')" class="mt-2" />
                    </div>

                    <!-- CAMPO promptGPT -->
                    <div>
                        <x-input-label for="promptGPT" :value="__('Prompt GPT')" />
                        <textarea 
                            id="promptGPT" 
                            name="promptGPT" 
                            rows="3" 
                            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        ></textarea>
                        <x-input-error :messages="$errors->get('promptGPT')" class="mt-2" />
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

    <script>
        document.getElementById('create-client-form').addEventListener('submit', function(event) {
            const checkboxes = document.querySelectorAll('input[name="id_utenti[]"]:checked');
            if (checkboxes.length === 0) {
                event.preventDefault();
                alert('Devi selezionare almeno un dipendente o fotografo.');
            }
        });
    </script>
</x-app-layout>
