@props(['name', 'id', 'options' => [], 'selected' => null, 'required' => false])

<select 
    name="{{ $name }}" 
    id="{{ $id }}" 
    class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
    {{ $required ? 'required' : '' }}>
    <option value="">{{ __('Seleziona un\'opzione') }}</option>
    @foreach($options as $value => $label)
        <option value="{{ $value }}" {{ (string) $value === (string) $selected ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>
