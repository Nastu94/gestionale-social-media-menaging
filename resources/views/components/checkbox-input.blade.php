<!-- resources/views/components/checkbox-input.blade.php -->

@props(['name', 'id', 'value', 'label', 'checked' => false])

<div>
    <label for="{{ $id }}" class="inline-flex items-center">
        <input type="checkbox" name="{{ $name }}[]" id="{{ $id }}" value="{{ $value }}" class="form-checkbox" {{ $checked ? 'checked' : '' }}>
        <span class="ml-2">{{ $label }}</span>
    </label>
</div>
