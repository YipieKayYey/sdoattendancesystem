<x-filament-panels::page>
    @php
        $profile = $this->getProfile();
        $hash = $this->getUniversalQrHash();
        $complete = $profile?->isComplete() ?? false;
    @endphp

    @if(!$profile)
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <div class="p-8 text-center">
                <p class="text-gray-600 dark:text-gray-300">No profile found. Please contact your administrator.</p>
            </div>
        </div>
    @elseif(!$complete)
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <div class="p-8">
                <h3 class="text-lg font-medium text-gray-950 dark:text-white mb-3">Complete Your Profile to Get Your QR Code</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Your profile is missing some information. Follow these steps:</p>
                <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 dark:text-gray-200 mb-6 max-w-md mx-auto text-left">
                    <li>Click the <strong>Edit Profile</strong> button below.</li>
                    <li>Fill in all required fields (name, contact info, school, etc.).</li>
                    <li>Draw your signature in the signature box and click <strong>Capture Signature</strong>.</li>
                    <li>Click <strong>Save Profile</strong>.</li>
                    <li>Return to this page — your QR code will appear.</li>
                </ol>
                <div class="text-center">
                    <a href="{{ \App\Filament\Attendee\Pages\EditProfile::getUrl() }}" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 fi-btn-color-primary fi-btn-size-lg fi-btn-filled rounded-lg fi-color-primary px-4 py-2 text-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500">
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <div class="p-8 text-center">
                <h3 class="text-lg font-medium text-gray-950 dark:text-white mb-2">Your Universal QR Code</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">Show this QR code when checking in at seminars. It works for all events.</p>
                <div class="inline-block p-6 bg-white rounded-xl border-2 border-gray-200 dark:border-gray-600">
                    {!! $this->getQrCodeHtml() !!}
                </div>
                <p class="mt-4 text-xs text-gray-600 dark:text-gray-300 font-mono">{{ $hash }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Save or screenshot this page for quick access.</p>
            </div>
        </div>
    @endif
</x-filament-panels::page>
