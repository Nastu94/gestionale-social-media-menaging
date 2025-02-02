@foreach ($commenti as $commento)
    @php
        $isCurrentUserAdminOrDipendente = in_array(auth()->user()->ruolo->nome, ['Amministratore', 'Dipendente']);
        $isCommentFromAdminOrDipendente = $commento->utente !== 'Cliente';

        // Determina se lo stile deve essere "verde" o "grigio"
        $isHighlighted = $isCurrentUserAdminOrDipendente === $isCommentFromAdminOrDipendente;
        $bgColor = $isHighlighted ? 'bg-green-100' : 'bg-gray-100';
        $textAlign = $isHighlighted ? 'text-right' : 'text-left';
        $textColor = $isHighlighted ? 'text-green-800' : 'text-gray-800';
    @endphp
    <div class="mb-2 p-2 rounded {{ $bgColor }} {{ $textAlign }}">
        <p class="font-semibold {{ $textColor }}">{{ $commento->utente }}</p>
        <p>{{ $commento->commento }}</p>
        <span class="text-xs text-gray-600">{{ $commento->data_testo }}</span>
    </div>
@endforeach