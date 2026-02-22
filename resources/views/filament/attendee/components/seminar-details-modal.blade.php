<div class="space-y-6 text-sm text-gray-700 dark:text-gray-200">
    {{-- Seminar Details --}}
    <div>
        <h4 class="text-base font-semibold text-gray-950 dark:text-white mb-3">Seminar Details</h4>
        <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Title</dt>
                <dd>{{ $seminar?->title ?? '—' }}</dd>
            </div>
            @if($seminar?->venue)
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Venue</dt>
                    <dd>{{ $seminar->venue }}</dd>
                </div>
            @endif
            @if($seminar?->date)
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Date</dt>
                    <dd>{{ $seminar->date->format('F j, Y') }}</dd>
                </div>
            @endif
            @if($seminar?->topic)
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Topic</dt>
                    <dd>{{ $seminar->topic }}</dd>
                </div>
            @endif
            @if($seminar?->room)
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Room</dt>
                    <dd>{{ $seminar->room }}</dd>
                </div>
            @endif
            @if($seminar?->formatted_time)
                <div>
                    <dt class="font-medium text-gray-500 dark:text-gray-400">Time</dt>
                    <dd>{{ $seminar->formatted_time }}</dd>
                </div>
            @endif
        </dl>
    </div>

    {{-- Check-in / Check-out Times --}}
    <div>
        <h4 class="text-base font-semibold text-gray-950 dark:text-white mb-3">Your Attendance</h4>
        @if($checkIns->isEmpty() && !$attendee->checked_in_at)
            <p class="text-gray-500 dark:text-gray-400">No check-in records yet.</p>
        @elseif($checkIns->isEmpty() && $attendee->checked_in_at)
            {{-- Legacy single-day: use attendee's checked_in_at/checked_out_at --}}
            <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4 bg-gray-50/50 dark:bg-white/5">
                <dl class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Checked In</dt>
                        <dd>{{ $attendee->checked_in_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Checked Out</dt>
                        <dd>{{ $attendee->checked_out_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                    </div>
                    @if($attendee->checked_in_at && $attendee->checked_out_at)
                        <div class="sm:col-span-2">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                            <dd>{{ $attendee->checked_in_at->diffForHumans($attendee->checked_out_at, true) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @else
            <div class="space-y-3">
                @foreach($checkIns as $checkIn)
                    <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4 bg-gray-50/50 dark:bg-white/5">
                        <div class="font-medium text-gray-950 dark:text-white mb-2">
                            @if($checkIn->seminarDay)
                                Day {{ $checkIn->seminarDay->day_number }} — {{ $checkIn->seminarDay->formatted_date }}
                                @if($checkIn->seminarDay->venue)
                                    <span class="text-gray-500 dark:text-gray-400">({{ $checkIn->seminarDay->venue }})</span>
                                @endif
                            @else
                                Attendance Record
                            @endif
                        </div>
                        <dl class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Checked In</dt>
                                <dd>{{ $checkIn->checked_in_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Checked Out</dt>
                                <dd>{{ $checkIn->checked_out_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                            </div>
                            @if($checkIn->checked_in_at && $checkIn->checked_out_at)
                                <div class="sm:col-span-2">
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                                    <dd>{{ $checkIn->checked_in_at->diffForHumans($checkIn->checked_out_at, true) }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
