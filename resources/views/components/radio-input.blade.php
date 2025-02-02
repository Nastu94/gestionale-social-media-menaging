<!-- resources/views/components/radio-input.blade.php -->

<div>
    @foreach($options as $value => $label)
        <div class="flex items-center">
            <input type="radio" name="{{ $name }}" id="{{ $id }}_{{ $value }}" value="{{ $value }}" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
            <label for="{{ $id }}_{{ $value }}" class="ml-2 block text-sm leading-5 text-gray-900">
                {{ $label }}
            </label>
        </div>
    @endforeach
</div>
