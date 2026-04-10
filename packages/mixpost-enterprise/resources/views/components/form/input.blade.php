@props(['error' => null])
<input {{ $attributes->merge(['class' => 'w-full rounded-md focus:border-primary-200 focus:ring-3 focus:ring-primary-200 focus:ring-opacity-50 outline-hidden transition-colors ease-in-out duration-200'.($error ? ' border-red-600' : ' border-stone-600')]) }} />
