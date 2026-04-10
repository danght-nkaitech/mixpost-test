<button {{ $attributes->merge(['class' => 'px-4 py-3 relative inline-flex items-center bg-primary-500 border border-transparent rounded-md font-medium text-xs text-primary-context uppercase tracking-widest hover:bg-primary-700 active:bg-primary-700 focus:border-primary-700 focus:shadow-outline-indigo disabled:bg-primary-200 disabled:text-gray-600 transition ease-in-out duration-200']) }}>
    {{ $slot }}
</button>
