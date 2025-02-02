<!-- resources/views/components/file-input.blade.php -->

<input type="file" name="{{ $name }}" id="{{ $id }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" {{ $required ? 'required' : '' }}>
