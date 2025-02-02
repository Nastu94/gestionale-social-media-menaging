<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Calendario Pubblicazioni') }}
        </h2>
    </x-slot>

    @if(auth()->user()->ruolo->nome !== 'Dipendente')
        <script>
            window.location = "/";
        </script>
    @endif
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900 rounded-lg">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $calendarEvents = $pubblicazioni->map(function ($pubblicazione) {
            return [
                'title' => $pubblicazione->cliente->nome,
                'start' => \Carbon\Carbon::parse($pubblicazione->data_pubblicazione)->format('Y-m-d\TH:i:s'),
                'classNames' => 'stato-' . $pubblicazione->stato_id,
                'url' => $pubblicazione->url
            ];
        })->toArray();
    @endphp

    <script>
        var calendarEvents = @json($calendarEvents);
        window.calendarEvents = calendarEvents;
    </script>
</x-app-layout>
