<x-filament-widgets::widget>
    <x-filament::section>
        <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Your Universal QR Code</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Show this when checking in at seminars</p>
            @php $profile = $this->getProfile(); $complete = $profile?->isComplete() ?? false; @endphp
            @if(!$profile)
                <p class="mt-4 text-gray-600 dark:text-gray-300">No profile found. Contact your administrator.</p>
            @elseif(!$complete)
                <p class="mt-4 text-gray-500 dark:text-gray-400">Your profile is incomplete. Go to <strong>My Profile</strong> → <strong>Edit Profile</strong>, fill in all fields, add your signature, and save.</p>
                <a href="{{ $this->getEditProfileUrl() }}" class="fi-btn fi-btn-color-primary fi-btn-size-sm fi-btn-filled mt-3 inline-grid">Edit Profile</a>
            @else
                <div class="mt-4 inline-block p-4 bg-white rounded-lg border border-gray-200 dark:border-gray-600">
                    {!! $this->getQrCodeHtml() !!}
                </div>
                <p class="mt-2 text-xs text-gray-600 dark:text-gray-300 font-mono">{{ $this->getUniversalQrHash() }}</p>
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    <x-filament::button
                        tag="a"
                        href="{{ route('attendee.universal-qr.preview') }}"
                        target="_blank"
                        color="primary"
                        size="sm"
                        icon="heroicon-o-eye"
                    >
                        Preview PDF
                    </x-filament::button>
                    <x-filament::button
                        tag="a"
                        href="{{ route('attendee.universal-qr.download') }}"
                        color="success"
                        size="sm"
                        icon="heroicon-o-arrow-down-tray"
                    >
                        Download PDF
                    </x-filament::button>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
