<x-filament-widgets::widget>
    <x-filament::section>
        <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Your Universal QR Code</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Show this when checking in at seminars</p>
            @if($this->getUniversalQrHash())
                <div class="mt-4 inline-block p-4 bg-white rounded-lg border border-gray-200 dark:border-gray-600">
                    {!! $this->getQrCodeHtml() !!}
                </div>
                <p class="mt-2 text-xs text-gray-500 font-mono">{{ $this->getUniversalQrHash() }}</p>
            @else
                <p class="mt-4 text-gray-500">No profile found. Contact your administrator.</p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
