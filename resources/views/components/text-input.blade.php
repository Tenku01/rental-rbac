@props(['disabled' => false])

<input 
    @disabled($disabled) 
    {{ $attributes->merge([
        'class' => 'border border-gray-300 bg-white text-gray-700 focus:border-cyan-500 focus:ring-cyan-500 rounded-md shadow-sm'
    ]) }}
>