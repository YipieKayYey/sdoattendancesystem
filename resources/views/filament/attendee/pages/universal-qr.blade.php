<x-filament-panels::page>
    @php
        $hash = $this->getUniversalQrHash();
    @endphp

    @if($hash)
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <div class="p-8 text-center">
                <h3 class="text-lg font-medium text-gray-950 dark:text-white mb-2">Your Universal QR Code</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Show this QR code when checking in at seminars. It works for all events.</p>
                <div class="inline-block p-6 bg-white rounded-xl border-2 border-gray-200 dark:border-gray-600">
                    {!! $this->getQrCodeHtml() !!}
                </div>
                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $hash }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Save or screenshot this page for quick access.</p>
            </div>
        </div>
    @else
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <div class="p-8 text-center">
                <p class="text-gray-500 dark:text-gray-400">No profile found. Please contact your administrator.</p>
            </div>
        </div>
    @endif
</x-filament-panels::page>
