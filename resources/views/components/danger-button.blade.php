<button {{ $attributes->merge(['type' => 'submit', 'class' => 'ui-btn bg-red-600 text-white hover:bg-red-700 focus:ring-red-200']) }}>
    {{ $slot }}
</button>
