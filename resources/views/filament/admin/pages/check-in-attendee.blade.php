<x-filament-panels::page>
    <div class="space-y-6">
        @if($this->seminar)
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-800">
                <h2 class="text-xl font-semibold mb-2 text-gray-900 dark:text-gray-100">{{ $this->seminar->title }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <p class="text-gray-600 dark:text-gray-300">
                        <span class="font-semibold">Date:</span> {{ $this->seminar->date->format('F j, Y') }}
                        @if(!$this->seminar->isMultiDay() && $this->seminar->time)
                            @ {{ $this->seminar->formatted_time }}
                        @endif
                    </p>
                    <p class="text-gray-600 dark:text-gray-300">
                        <span class="font-semibold">Registered:</span> 
                        {{ $this->seminar->attendees()->count() }} 
                        @if($this->seminar->is_open)
                            (Open Seminar - Unlimited)
                        @else
                            / {{ $this->seminar->capacity }}
                        @endif
                    </p>
                </div>
                
                @if($this->seminar->isMultiDay())
                    <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-lg p-4 mt-4">
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">Multi-Day Seminar</h3>
                        <div class="space-y-2">
                            @foreach($this->seminar->days as $day)
                                <div class="flex items-center justify-between">
                                    <span class="text-blue-800 dark:text-blue-200">
                                        <span class="font-semibold">Day {{ $day->day_number }}:</span> 
                                        {{ $day->formatted_date }}
                                        @if($day->start_time)
                                            <span class="text-sm text-blue-700 dark:text-blue-300">({{ $day->formatted_time }})</span>
                                        @endif
                                    </span>
                                    @if($this->currentDay && $this->currentDay->id === $day->id)
                                        <span class="bg-blue-600 dark:bg-blue-500 text-white px-2 py-1 rounded text-sm font-semibold">Current Day</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Scan QR Code or Enter Ticket Hash</h3>
            
            @if($this->seminar && $this->selectedDayId)
                @php
                    $selectedDay = $this->seminar->days()->find($this->selectedDayId);
                @endphp
                @if($selectedDay)
                    <div class="mb-4 p-3 bg-blue-100 dark:bg-blue-950/40 border border-blue-300 dark:border-blue-900/60 rounded-lg">
                        <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">
                            <span class="inline-block mr-2">📅</span>
                            <span class="text-blue-700 dark:text-blue-200">Currently scanning for: Day {{ $selectedDay->day_number }} - {{ $selectedDay->formatted_date }}</span>
                            @if($selectedDay->start_time)
                                <span class="text-blue-600 dark:text-blue-300">({{ $selectedDay->formatted_time }})</span>
                            @endif
                        </p>
                    </div>
                @endif
            @endif
            
            <form wire:submit.prevent="checkIn" class="space-y-4">
                {{ $this->form }}
                
                @if($this->seminar && $this->selectedDayId)
                    @php
                        $selectedDay = $this->seminar->days()->find($this->selectedDayId);
                    @endphp
                    @if($selectedDay)
                        <div class="p-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900/60 rounded-lg">
                            <p class="text-sm text-green-800 dark:text-green-200">
                                <span class="font-semibold">✓ Selected Day:</span> Day {{ $selectedDay->day_number }} - {{ $selectedDay->formatted_date }}
                            </p>
                        </div>
                    @endif
                @endif
                
                <div class="flex gap-4">
                    <x-filament::button 
                        type="submit" 
                        size="sm"
                    >
                        Check In
                    </x-filament::button>
                    
                    @if($this->seminar)
                        <x-filament::button 
                            wire:click="backToSeminar"
                            variant="outline"
                            size="sm"
                        >
                            Back to Seminar
                        </x-filament::button>
                    @endif
                </div>
            </form>

            {{-- Day Selection Modal for Multi-Day Seminars --}}
            @if($this->seminar && $this->seminar->days()->count() > 1)
                <x-filament::modal wire:model="showDayModal" width="md">
                    <x-slot name="heading">
                        Select Day for Check-In
                    </x-slot>
                    
                    <x-slot name="description">
                        This is a multi-day seminar. Please select which day you want to check in this attendee for.
                    </x-slot>
                    
                    <form wire:submit.prevent="selectDayAndCheckIn" class="space-y-4">
                        {{ $this->dayModalForm }}
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <x-filament::button 
                                type="button"
                                variant="outline"
                                wire:click="closeDayModal"
                            >
                                Cancel
                            </x-filament::button>
                            <x-filament::button type="submit">
                                Check In
                            </x-filament::button>
                        </div>
                    </form>
                </x-filament::modal>
            @endif

            <!-- QR Code Scanner -->
            <div class="mt-6">
                <div id="qr-reader" class="w-full max-w-md mx-auto"></div>
            </div>
        </div>

        @if($this->lastCheckedIn)
            <div class="bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900/60 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">Last Checked In</h3>
                <div class="space-y-2 text-green-800 dark:text-green-200">
                    <p><span class="font-semibold">Name:</span> {{ $this->lastCheckedIn->full_name ?: $this->lastCheckedIn->name }}</p>
                    <p><span class="font-semibold">Email:</span> {{ $this->lastCheckedIn->email }}</p>
                    <p><span class="font-semibold">Personnel Type:</span> {{ $this->lastCheckedIn->personnel_type === 'teaching' ? 'Teaching' : ($this->lastCheckedIn->personnel_type === 'non_teaching' ? 'Non-Teaching' : '—') }}</p>
                    @if($this->lastCheckedIn->hasSignature())
                        <p><span class="font-semibold">Signature:</span> <span class="text-green-700 dark:text-green-300">✓ Verified</span> @if($this->lastCheckedIn->signature_timestamp)({{ $this->lastCheckedIn->signature_timestamp->format('M j, Y g:i A') }})@endif</p>
                    @else
                        <p><span class="font-semibold">Signature:</span> <span class="text-yellow-700 dark:text-yellow-300">⚠ Not provided</span></p>
                    @endif
                    <p><span class="font-semibold">Ticket Hash:</span> <code class="bg-green-100 dark:bg-green-900/40 px-2 py-1 rounded">{{ $this->lastCheckedIn->ticket_hash }}</code></p>
                    
                    @if($this->lastCheckInRecord && $this->seminar && $this->seminar->isMultiDay())
                        {{-- Multi-day: Show per-day check-in/check-out --}}
                        <div class="mt-3 pt-3 border-t border-green-300">
                            <p class="font-semibold mb-2">Day {{ $this->lastCheckInRecord->seminarDay->day_number }} Attendance:</p>
                            <p><span class="font-semibold">Checked In:</span> {{ $this->lastCheckInRecord->checked_in_at->format('M j, Y g:i A') }}</p>
                            @if($this->lastCheckInRecord->checked_out_at)
                                <p><span class="font-semibold">Checked Out:</span> {{ $this->lastCheckInRecord->checked_out_at->format('M j, Y g:i A') }}</p>
                                <p><span class="font-semibold">Duration:</span> {{ $this->lastCheckInRecord->checked_in_at->diffForHumans($this->lastCheckInRecord->checked_out_at, true) }}</p>
                            @else
                                <p><span class="font-semibold">Checked Out:</span> <span class="text-gray-600 dark:text-gray-400">Not checked out yet</span></p>
                            @endif
                        </div>
                    @else
                        {{-- Single-day: Show simple check-in/check-out --}}
                        <p><span class="font-semibold">Checked In At:</span> {{ $this->lastCheckedIn->checked_in_at ? $this->lastCheckedIn->checked_in_at->format('M j, Y g:i A') : 'Not checked in' }}</p>
                        @if($this->lastCheckedIn->checked_out_at)
                            <p><span class="font-semibold">Checked Out At:</span> {{ $this->lastCheckedIn->checked_out_at->format('M j, Y g:i A') }}</p>
                            @if($this->lastCheckedIn->checked_in_at)
                                <p><span class="font-semibold">Duration:</span> {{ $this->lastCheckedIn->checked_in_at->diffForHumans($this->lastCheckedIn->checked_out_at, true) }}</p>
                            @endif
                        @else
                            <p><span class="font-semibold">Checked Out At:</span> <span class="text-gray-600 dark:text-gray-400">Not checked out yet</span></p>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        let html5QrcodeScanner = null;
        
        function startScanner() {
            if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
                return;
            }

            const qrCodeSuccessCallback = async (decodedText, decodedResult) => {
                await html5QrcodeScanner.stop();
                // Use the dedicated method to set hash and check in
                @this.call('setTicketHashFromScan', decodedText).then(() => {
                    // Restart scanner after check-in
                    setTimeout(() => startScanner(), 2000);
                });
            };

            const qrCodeErrorCallback = (errorMessage) => {
                // Ignore errors, just keep scanning
            };

            html5QrcodeScanner = new Html5Qrcode("qr-reader");
            
            html5QrcodeScanner.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                qrCodeSuccessCallback,
                qrCodeErrorCallback
            ).catch(err => {
                console.error('Failed to start QR scanner:', err);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            startScanner();
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().catch(() => {});
            }
        });
    </script>
    @endpush
</x-filament-panels::page>
