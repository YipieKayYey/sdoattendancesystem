<x-filament-panels::page>
    <div class="space-y-6">
        @if($this->seminar)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-2">{{ $this->seminar->title }}</h2>
                <p class="text-gray-600">
                    <span class="font-semibold">Date:</span> {{ $this->seminar->date->format('F j, Y') }}
                </p>
                <p class="text-gray-600">
                    <span class="font-semibold">Registered:</span> 
                    {{ $this->seminar->attendees()->count() }} 
                    @if($this->seminar->is_open)
                        (Open Seminar - Unlimited)
                    @else
                        / {{ $this->seminar->capacity }}
                    @endif
                </p>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Scan QR Code or Enter Ticket Hash</h3>
            
            <form wire:submit="checkIn" class="space-y-4">
                {{ $this->form }}
                
                <div class="flex gap-4">
                    <x-filament::button type="submit" size="lg">
                        Check In
                    </x-filament::button>
                    
                    @if($this->seminar)
                        <x-filament::button 
                            href="{{ route('filament.admin.resources.seminars.edit', $this->seminar->id) }}"
                            variant="outline"
                            size="lg"
                        >
                            Back to Seminar
                        </x-filament::button>
                    @endif
                </div>
            </form>

            <!-- QR Code Scanner -->
            <div class="mt-6">
                <div id="qr-reader" class="w-full max-w-md mx-auto"></div>
            </div>
        </div>

        @if($this->lastCheckedIn)
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-900 mb-2">Last Checked In</h3>
                <div class="space-y-2 text-green-800">
                    <p><span class="font-semibold">Name:</span> {{ $this->lastCheckedIn->name }}</p>
                    <p><span class="font-semibold">Email:</span> {{ $this->lastCheckedIn->email }}</p>
                    <p><span class="font-semibold">Ticket Hash:</span> <code class="bg-green-100 px-2 py-1 rounded">{{ $this->lastCheckedIn->ticket_hash }}</code></p>
                    <p><span class="font-semibold">Checked In At:</span> {{ $this->lastCheckedIn->checked_in_at->format('M j, Y g:i A') }}</p>
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
