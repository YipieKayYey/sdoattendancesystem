<x-filament-panels::page>
    @php
        $profile = $this->getProfile();
    @endphp

    @if($profile)
        <div class="space-y-6">
            <x-filament::section heading="Personal Information">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Personnel Type</dt>
                        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->personnel_type ? ucfirst(str_replace('_', ' ', $profile->personnel_type)) : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sex</dt>
                        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->sex ? ucfirst($profile->sex) : '—' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            <x-filament::section heading="Contact & Work">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mobile Phone</dt>
                        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->mobile_phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Position</dt>
                        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->position ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">School/Office/Agency</dt>
                        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->school_office_agency ?? $profile->school_other ?? ($profile->school?->name ?? '—') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">PRC License No.</dt>
                        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->prc_license_no ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">PRC Expiry</dt>
                        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->prc_license_expiry?->format('F j, Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            <x-filament::section heading="Signature">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if($profile->hasSignature())
                        Signature Provided — {{ $profile->signature_timestamp?->format('F j, Y') ?? '—' }}
                    @else
                        No signature on file. Add one in Edit Profile.
                    @endif
                </p>
            </x-filament::section>
        </div>
    @else
        <x-filament::section>
            <p class="text-gray-500 dark:text-gray-400">No profile found. Please contact your administrator.</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
