<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assets di ') . $cliente->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-gray-900 rounded-lg">
                <!-- Pulsante per aggiungere un nuovo asset -->
                <div class="mb-4 flex justify-between items-center mb-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center block bg-white border border-gray-200 rounded-lg shadow p-4">
                        <div class="mr-4 p-3">
                            <img src="/logoClienti/{{ $cliente->logo_cliente }}" alt="Logo Cliente" class="w-16 h-16 object-cover rounded-full">
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold">{{ ucwords($cliente->nome) }}</h3>
                        </div>
                    </a>
                    <a href="{{ route('assets.create', $cliente) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Aggiungi Asset
                    </a>
                </div>

                <!-- Tabella per mostrare gli asset -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nome Asset</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Password</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($assets as $asset)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ $asset->nome_assets }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ $asset->username }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="password-mask">{{ $asset->password }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-start space-x-3">
                                            <!-- Pulsanti per modificare o eliminare un asset -->
                                            <a href="{{ route('assets.edit', ['cliente' => $cliente, 'asset' => $asset]) }}" class="text-indigo-600 hover:text-indigo-900">
                                                <x-heroicon-o-pencil class="w-5 h-5" />
                                            </a>
                                            <form action="{{ route('assets.destroy', ['cliente' => $cliente, 'asset' => $asset]) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
