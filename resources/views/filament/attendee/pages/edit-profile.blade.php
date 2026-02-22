<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-950 dark:text-white">Signature</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Draw your signature in the box below. Your signature will be used when you check in at seminars.</p>

                <div class="mt-4 flex items-start gap-2">
                    <input type="checkbox" id="profileSignatureConsent" wire:model="signatureConsent" class="mt-1 rounded border-gray-300 dark:border-white/20">
                    <label for="profileSignatureConsent" class="text-sm text-gray-700 dark:text-gray-200">
                        I certify that the information provided is true and correct, and I consent to the collection of my personal data and electronic signature in accordance with the Privacy Policy and E-Signature Collection notice.
                    </label>
                </div>

                <label class="mt-4 block text-sm font-semibold text-gray-950 dark:text-white">Draw Your Signature</label>
                <div class="mt-2" x-data="{ init: false }" x-init="
                    if (!init) {
                        const script = document.createElement('script');
                        script.src = 'https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js';
                        script.onload = () => { init = true; };
                        document.head.appendChild(script);
                    }
                ">
                    <div class="border-2 border-gray-200 rounded-lg bg-white dark:bg-gray-900">
                        <canvas id="profileSignaturePad" wire:ignore class="w-full h-40 cursor-crosshair bg-white dark:bg-gray-900" style="touch-action: none;"></canvas>
                    </div>
                    <div class="mt-2 flex gap-2">
                        <button type="button" onclick="profileClearSignature()" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 fi-btn-color-gray fi-btn-size-sm fi-btn-outlined rounded-lg fi-color-gray px-3 py-2 text-sm inline-grid shadow-sm bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10">
                            Clear
                        </button>
                        <button type="button" onclick="profileSaveSignature()" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus:ring-2 fi-btn-color-primary fi-btn-size-sm fi-btn-filled rounded-lg fi-color-primary px-3 py-2 text-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400">
                            Capture Signature
                        </button>
                    </div>
                    @if($signatureData)
                        <p class="mt-2 text-sm text-green-600 dark:text-green-400">✓ Signature captured</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" size="lg">Save Profile</x-filament::button>
        </div>
    </form>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('profileSignaturePad');
            if (canvas && typeof SignaturePad !== 'undefined') {
                window.profileSignaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)',
                    minWidth: 1,
                    maxWidth: 3
                });
                function resize() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);
                    if (window.profileSignaturePad) window.profileSignaturePad.clear();
                }
                resize();
                window.addEventListener('resize', resize);
            }
        });
        function profileClearSignature() {
            if (window.profileSignaturePad) {
                window.profileSignaturePad.clear();
                @this.set('signatureData', null);
            }
        }
        function profileSaveSignature() {
            if (window.profileSignaturePad && !window.profileSignaturePad.isEmpty()) {
                const canvas = document.getElementById('profileSignaturePad');
                const signatureImage = window.profileSignaturePad.toDataURL('image/png');
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = canvas.width;
                tempCanvas.height = canvas.height;
                const tempCtx = tempCanvas.getContext('2d');
                tempCtx.fillStyle = 'rgb(255, 255, 255)';
                tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
                const img = new Image();
                img.onload = function() {
                    tempCtx.drawImage(img, 0, 0);
                    tempCtx.save();
                    tempCtx.translate(tempCanvas.width / 2, tempCanvas.height / 2);
                    tempCtx.rotate(-45 * Math.PI / 180);
                    tempCtx.font = 'bold 14px Arial';
                    tempCtx.fillStyle = 'rgba(0, 0, 0, 0.1)';
                    tempCtx.textAlign = 'center';
                    tempCtx.textBaseline = 'middle';
                    tempCtx.fillText('Valid for AMS Seminars', 0, 0);
                    tempCtx.restore();
                    const data = tempCanvas.toDataURL('image/png');
                    @this.set('signatureData', data);
                };
                img.src = signatureImage;
            } else {
                alert('Please draw your signature first.');
            }
        }
    </script>
    @endpush
</x-filament-panels::page>
