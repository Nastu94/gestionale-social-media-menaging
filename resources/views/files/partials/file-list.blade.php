<div class="modal-content bg-white rounded shadow-lg p-4">
    <h3 class="text-lg font-semibold mb-4">File disponibili su Nextcloud</h3>
    <ul>
        @foreach ($files as $path => $info)
            <li>
                <button class="select-file text-blue-500 underline" data-path="{{ $path }}">
                    {{ $path }}
                </button>
            </li>
        @endforeach
    </ul>
</div>
