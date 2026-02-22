@props(['profile'])

@if($profile)
    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
        <div>
            <dt class="font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
            <dd class="mt-0.5 text-gray-950 dark:text-white">{{ $profile->full_name ?: '—' }}</dd>
        </div>
        <div>
            <dt class="font-medium text-gray-500 dark:text-gray-400">Personnel Type</dt>
            <dd class="mt-0.5 text-gray-950 dark:text-white">{{ $profile->personnel_type ? ucfirst(str_replace('_', ' ', $profile->personnel_type)) : '—' }}</dd>
        </div>
        <div>
            <dt class="font-medium text-gray-500 dark:text-gray-400">Sex</dt>
            <dd class="mt-0.5 text-gray-950 dark:text-white">{{ $profile->sex ? ucfirst($profile->sex) : '—' }}</dd>
        </div>
        <div>
            <dt class="font-medium text-gray-500 dark:text-gray-400">Mobile Phone</dt>
            <dd class="mt-0.5 text-gray-950 dark:text-white">{{ $profile->mobile_phone ?? '—' }}</dd>
        </div>
        <div>
            <dt class="font-medium text-gray-500 dark:text-gray-400">Position</dt>
            <dd class="mt-0.5 text-gray-950 dark:text-white">{{ $profile->position ?? '—' }}</dd>
        </div>
        <div>
            <dt class="font-medium text-gray-500 dark:text-gray-400">School/Office/Agency</dt>
            <dd class="mt-0.5 text-gray-950 dark:text-white">{{ $profile->school_office_agency ?? $profile->school_other ?? ($profile->school?->name ?? '—') }}</dd>
        </div>
        <div>
            <dt class="font-medium text-gray-500 dark:text-gray-400">PRC License No.</dt>
            <dd class="mt-0.5 text-gray-950 dark:text-white">{{ $profile->prc_license_no ?? '—' }}</dd>
        </div>
        <div>
            <dt class="font-medium text-gray-500 dark:text-gray-400">PRC Expiry</dt>
            <dd class="mt-0.5 text-gray-950 dark:text-white">{{ $profile->prc_license_expiry?->format('F j, Y') ?? '—' }}</dd>
        </div>
    </dl>
    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Filled in by the user in their dashboard.</p>
@else
    <p class="text-gray-500 dark:text-gray-400 text-sm">Profile not yet completed by the user.</p>
@endif
