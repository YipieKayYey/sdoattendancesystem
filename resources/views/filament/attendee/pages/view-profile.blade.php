<x-filament-panels::page>
    <div class="space-y-6">
        @php
            $profile = $this->getProfile();
        @endphp
        @if($profile)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">Full Name</dt>
                            <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">Personnel Type</dt>
                            <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->personnel_type ? ucfirst(str_replace('_', ' ', $profile->personnel_type)) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">Sex</dt>
                            <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->sex ? ucfirst($profile->sex) : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">Mobile Phone</dt>
                            <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->mobile_phone ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">Position</dt>
                            <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->position ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">School/Office/Agency</dt>
                            <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->school_office_agency ?? $profile->school_other ?? ($profile->school?->name ?? '—') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">PRC License No.</dt>
                            <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->prc_license_no ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">PRC Expiry</dt>
                            <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $profile->prc_license_expiry?->format('F j, Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                    @if($profile->hasSignature())
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">Signature</dt>
                            <dd class="mt-1 text-sm text-gray-950 dark:text-white">
                                Signature provided — {{ $profile->signature_timestamp?->format('F j, Y g:i A') ?? '—' }}
                            </dd>
                        </div>
                    @else
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-gray-600 dark:text-gray-300">Signature not yet provided.</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-300">No profile found. Please contact your administrator.</p>
        @endif
    </div>
</x-filament-panels::page>
