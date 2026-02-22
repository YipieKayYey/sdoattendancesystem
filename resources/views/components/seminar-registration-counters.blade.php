@php
    $registered = $seminar->attendees()->count();
    $capacity = $seminar->is_open ? null : $seminar->capacity;
    $available = $seminar->is_open ? null : max(0, $capacity - $registered);
@endphp
<div class="flex flex-wrap items-center gap-6">
    @if (!$seminar->is_open)
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Available Spots</span>
            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $available }}</span>
        </div>
    @endif
    <div class="flex items-center gap-2">
        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Registered Attendees</span>
        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $registered }} / {{ $seminar->is_open ? 'Unlimited' : $capacity }}</span>
    </div>
    <a href="{{ route('admin.seminars.live-dashboard', ['id' => $seminar->id]) }}" target="_blank"
       class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        View Live Dashboard
    </a>
</div>
