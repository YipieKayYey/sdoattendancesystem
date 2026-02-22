<div wire:poll.5s class="min-h-screen flex flex-col items-center justify-center p-6 sm:p-8" style="background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(240,248,255,0.98) 100%);">
    @if($seminar)
        {{-- Logos --}}
        <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-8 mb-6">
            <img src="{{ asset('images/sdodesignlogo.png') }}" alt="SDO Balanga City" class="h-14 sm:h-16 w-auto object-contain">
            <img src="{{ asset('images/depedlogo.png') }}" alt="Department of Education" class="h-12 sm:h-14 w-auto object-contain">
            <img src="{{ asset('images/BagongPilipinasLogo.png') }}" alt="Bagong Pilipinas" class="h-10 sm:h-12 w-auto object-contain">
            <img src="{{ asset('images/Philippinequal.png') }}" alt="Philippine Quality Award" class="h-10 sm:h-12 w-auto object-contain">
            <img src="{{ asset('images/pgs-logo.png') }}" alt="Performance Governance System" class="h-10 sm:h-12 w-auto object-contain">
        </div>

        {{-- Seminar title --}}
        <h1 class="text-2xl sm:text-3xl font-bold mb-2 text-center max-w-4xl" style="color: #1b297a;">
            {{ $seminar->title }}
        </h1>
        <p class="text-sm mb-4" style="color: #1b297a; opacity: 0.8;">Live attendance • Updates every 5 seconds</p>

        {{-- Day selector for multi-day seminars --}}
        @if($seminar->is_multi_day && $seminar->days()->exists())
            <div class="mb-6">
                <label for="day-select" class="block text-sm font-medium mb-2" style="color: #1b297a;">Select Day</label>
                <select id="day-select" wire:model.live="selectedDayId"
                        class="rounded-lg border-2 px-4 py-2 text-sm font-medium focus:ring-2 focus:ring-offset-1 min-w-[200px]"
                        style="border-color: #18309b; color: #1b297a;">
                    @foreach($seminar->days()->orderBy('day_number')->get() as $day)
                        <option value="{{ $day->id }}">
                            Day {{ $day->day_number }} - {{ $day->formatted_date }}
                            @if($day->start_time)
                                ({{ $day->formatted_time }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @if($selectedDayId)
                    @php $selectedDay = $seminar->days()->find($selectedDayId); @endphp
                    @if($selectedDay)
                        <p class="text-xs mt-1" style="color: #1b297a; opacity: 0.7;">Showing: {{ $selectedDay->venue ? $selectedDay->venue : 'Day ' . $selectedDay->day_number }}</p>
                    @endif
                @endif
            </div>
        @endif

        {{-- Stats cards (white with primary-blue border like homepage) --}}
        <div class="flex flex-wrap justify-center gap-6 mb-10">
            <div class="bg-white rounded-xl px-6 py-5 min-w-[160px] text-center shadow-lg border-2" style="border-color: #18309b;">
                <p class="text-xs sm:text-sm font-medium uppercase tracking-wider mb-1" style="color: #1b297a;">Registered</p>
                <p class="text-3xl sm:text-4xl font-bold" style="color: #18309b;">{{ $registeredCount }}</p>
                @if(!$isOpen && $capacity > 0)
                    <p class="text-xs sm:text-sm mt-1" style="color: #1b297a; opacity: 0.7;">of {{ $capacity }} capacity</p>
                @else
                    <p class="text-xs sm:text-sm mt-1" style="color: #1b297a; opacity: 0.7;">Unlimited capacity</p>
                @endif
            </div>
            <div class="bg-white rounded-xl px-6 py-5 min-w-[160px] text-center shadow-lg border-2" style="border-color: #00bcd4;">
                <p class="text-xs sm:text-sm font-medium uppercase tracking-wider mb-1" style="color: #1b297a;">
                    Checked In
                    @if($seminar->is_multi_day && $selectedDayId)
                        @php $selDay = $seminar->days()->find($selectedDayId); @endphp
                        @if($selDay)
                            <span class="normal-case font-normal">(Day {{ $selDay->day_number }})</span>
                        @endif
                    @endif
                </p>
                <p class="text-3xl sm:text-4xl font-bold" style="color: #00bcd4;">{{ $checkedInCount }}</p>
                @if($registeredCount > 0)
                    <p class="text-xs sm:text-sm mt-1" style="color: #1b297a; opacity: 0.7;">{{ round($checkedInCount / $registeredCount * 100, 1) }}% of registered</p>
                @else
                    <p class="text-xs sm:text-sm mt-1" style="color: #1b297a; opacity: 0.7;">—</p>
                @endif
            </div>
        </div>

        {{-- Circle graph (theme colors: accent-cyan=checked in, primary-blue=registered) --}}
        <div class="relative w-64 h-64 sm:w-80 sm:h-80 mb-6">
            @php
                $radius = 140;
                $circumference = 2 * M_PI * $radius;
                $maxForRing = $isOpen ? max($registeredCount, 1) : max($capacity, 1);
                $registeredPercent = $isOpen ? 100 : min(100, $registeredCount / $maxForRing * 100);
                $checkedInPercent = $registeredCount > 0 ? ($checkedInCount / $registeredCount * 100) : 0;
                $checkedInLength = ($checkedInPercent / 100) * $circumference;
                $registeredOnlyLength = max(0, ($registeredPercent - $checkedInPercent) / 100 * $circumference);
            @endphp
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 320 320">
                {{-- Background ring (light gray) --}}
                <circle cx="160" cy="160" r="{{ $radius }}" fill="none" stroke="#e2e8f0" stroke-width="24"/>
                {{-- Checked-in segment (accent cyan) - first segment from top --}}
                <circle cx="160" cy="160" r="{{ $radius }}" fill="none" stroke="#00bcd4" stroke-width="24"
                        stroke-dasharray="{{ $checkedInLength }} {{ $circumference - $checkedInLength }}"
                        stroke-dashoffset="0" stroke-linecap="round" class="transition-all duration-700"/>
                {{-- Registered but not checked in (primary blue) - segment after cyan --}}
                <circle cx="160" cy="160" r="{{ $radius }}" fill="none" stroke="#18309b" stroke-width="24"
                        stroke-dasharray="{{ $registeredOnlyLength }} {{ $circumference - $registeredOnlyLength }}"
                        stroke-dashoffset="{{ -$checkedInLength }}" stroke-linecap="round" class="transition-all duration-700"/>
            </svg>
            {{-- Center text --}}
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-4xl sm:text-5xl font-bold" style="color: #2d1360;">{{ $checkedInCount }}</span>
                <span class="text-sm mt-1" style="color: #1b297a; opacity: 0.8;">checked in</span>
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap justify-center gap-6 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full" style="background-color: #00bcd4;"></span>
                <span style="color: #1b297a;">Checked In</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full" style="background-color: #18309b;"></span>
                <span style="color: #1b297a;">Registered (not yet checked in)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-slate-300"></span>
                <span style="color: #1b297a;">Available</span>
            </div>
        </div>

        {{-- Last updated --}}
        <p class="text-xs mt-8" style="color: #1b297a; opacity: 0.6;">{{ now()->format('g:i:s A') }}</p>
    @else
        <div class="text-slate-400">Seminar not found.</div>
    @endif
</div>
