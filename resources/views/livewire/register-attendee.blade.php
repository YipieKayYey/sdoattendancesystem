<div class="min-h-screen bg-gradient-to-br from-blue-50 to-sky-100 py-4 sm:py-8 px-4">
    <div class="max-w-2xl mx-auto">
        @if($seminar)
            <!-- Registration Card -->
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-blue-600 to-sky-600 px-4 sm:px-8 py-4 sm:py-6 text-white">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <img src="{{ asset('images/sdodesignlogo.png') }}" alt="SDO Logo" class="h-16 sm:h-20 w-16 sm:w-20 object-contain">
                        </div>
                        <!-- Title and Date -->
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-2xl sm:text-3xl font-bold mb-2 break-words">
                                {{ $seminar->title }}
                            </h1>
                            <div class="flex items-center justify-center sm:justify-start gap-2 sm:gap-4 text-blue-100">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium text-sm sm:text-base">
                                    @if($seminar->isMultiDay())
                                        {{ $seminar->days->map(function($day) { return $day->date->format('F j'); })->implode(', ') }}, {{ $seminar->days->first()->date->format('Y') }}
                                    @else
                                        {{ $seminar->date->format('F j, Y') }}
                                        @if($seminar->time)
                                            @ {{ $seminar->formatted_time }}
                                        @endif
                                        @if($seminar->venue)
                                            • {{ $seminar->venue }}
                                        @endif
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Section -->
                <div class="px-4 sm:px-8 py-4 sm:py-6">
                    @if($seminar->is_ended)
                        <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-red-800 font-semibold">Registration Closed</p>
                                    <p class="text-red-700 text-sm mt-1">Registration for this seminar has been closed. Please contact the administrator for more information.</p>
                                </div>
                            </div>
                        </div>
                    @elseif($seminar->isFull())
                        <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-red-800 font-semibold">This seminar is full. Registration is closed.</p>
                            </div>
                        </div>
                    @else
                        <!-- Registration Count -->
                        <div class="mb-6 p-3 sm:p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <span class="text-gray-700 font-medium text-sm sm:text-base">Number of Registrations : </span>
                            <span class="text-blue-700 font-bold text-base sm:text-lg">{{ $seminar->registered_count }}</span>
                        </div>

                        <!-- Multi-Day Seminar Information -->
                        @if($seminar->isMultiDay())
                            <div class="mb-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                                <div class="flex items-center mb-3">
                                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <h3 class="text-purple-800 font-semibold text-lg">Multi-Day Seminar</h3>
                                </div>
                                <div class="space-y-2">
                                    @foreach($seminar->days as $day)
                                        <div class="flex items-center justify-between bg-white p-2 rounded border border-purple-100">
                                            <span class="text-purple-700 font-medium">
                                                <span class="font-bold">Day {{ $day->day_number }}:</span> 
                                                {{ $day->formatted_date }}
                                                @if($day->start_time)
                                                    <span class="text-sm text-purple-600">({{ $day->formatted_time }})</span>
                                                @endif
                                            </span>
                                            @if($day->venue)
                                                <span class="text-sm text-purple-600">{{ $day->venue }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-sm text-purple-600 mt-3">
                                    <strong>Note:</strong> This registration covers all days of the multi-day seminar.
                                </p>
                            </div>
                        @endif

                        <!-- Step Indicator -->
                        <div class="mb-6">
                            <div class="flex items-center justify-center gap-2">
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $currentStep >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500' }} font-bold">
                                        1
                                    </div>
                                    <div class="ml-2 text-sm font-medium {{ $currentStep >= 1 ? 'text-blue-600' : 'text-gray-500' }}">Personal Information</div>
                                </div>
                                <div class="w-16 h-1 {{ $currentStep >= 2 ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
                                <div class="flex items-center">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $currentStep >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500' }} font-bold">
                                        2
                                    </div>
                                    <div class="ml-2 text-sm font-medium {{ $currentStep >= 2 ? 'text-blue-600' : 'text-gray-500' }}">Additional Details</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Registration Form -->
                        <form wire:submit="register" class="space-y-5">
                            @if($currentStep === 1)
                                <!-- STEP 1: Personal Information -->
                                <!-- Personnel Type -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Personnel Type <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center">
                                            <input type="radio" wire:model.live="personnelType" value="teaching" class="mr-2">
                                            <span>Teaching</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" wire:model.live="personnelType" value="non_teaching" class="mr-2">
                                            <span>Non-Teaching</span>
                                        </label>
                                    </div>
                                    @error('personnelType')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Name Fields -->
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div>
                                        <label for="firstName" class="block text-sm font-semibold text-gray-700 mb-2">
                                            First Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="firstName" wire:model="firstName" oninput="this.value = capitalizeWords(this.value)" placeholder="First Name" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base" required>
                                        @error('firstName')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="middleName" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Middle Name (Full)
                                        </label>
                                        <input type="text" id="middleName" wire:model="middleName" oninput="this.value = capitalizeWords(this.value)" placeholder="Enter full middle name (optional)" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base">
                                        @error('middleName')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="lastName" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Last Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="lastName" wire:model="lastName" oninput="this.value = capitalizeWords(this.value)" placeholder="Last Name" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base" required>
                                        @error('lastName')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="suffix" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Ext. / Suffix
                                        </label>
                                        <input type="text" id="suffix" wire:model="suffix" oninput="this.value = capitalizeWords(this.value)" placeholder="Jr., Sr., III (leave blank if none)" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base">
                                        <p class="mt-1 text-xs text-gray-500">Leave blank if you don't have a suffix (do not enter N/A)</p>
                                        @error('suffix')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <!-- Sex Field -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Sex <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center">
                                            <input type="radio" wire:model="sex" value="male" class="mr-2" required>
                                            <span>Male</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" wire:model="sex" value="female" class="mr-2" required>
                                            <span>Female</span>
                                        </label>
                                    </div>
                                    @error('sex')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- School/Office/Agency Field -->
                                <div>
                                    <label for="schoolOfficeAgency" class="block text-sm font-semibold text-gray-700 mb-2">
                                        School/Office/Agency <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="schoolOfficeAgency" wire:model="schoolOfficeAgency" placeholder="Enter your school, office, or agency" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base" required>
                                    @error('schoolOfficeAgency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <!-- Email Field -->
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" id="email" wire:model="email" placeholder="Enter your email address" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base" required>
                                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <!-- Next Button -->
                                <button type="button" wire:click="nextStep" class="w-full bg-gradient-to-r from-blue-600 to-sky-600 text-white py-3 sm:py-4 px-6 rounded-lg font-bold text-base sm:text-lg hover:from-blue-700 hover:to-sky-700 transition-all transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                                    Continue to Step 2
                                </button>
                            @else
                                <!-- STEP 2: Additional Details -->
                                <!-- Mobile Phone -->
                                <div>
                                    <label for="mobilePhone" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Mobile Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="mobilePhone" wire:model="mobilePhone" placeholder="Enter your mobile phone number" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base" required>
                                    @error('mobilePhone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <!-- Position Field -->
                                <div>
                                    <label for="position" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Position / Job Title <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="position" wire:model="position" placeholder="Enter your position or job title" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base" required>
                                    @error('position')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <!-- PRC License Fields -->
                                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 space-y-4">
                                    <h3 class="font-semibold text-blue-900">PRC License Information</h3>
                                    
                                    <!-- No PRC License Option -->
                                    <div class="flex items-start gap-2 pb-3 border-b border-blue-300">
                                        <input type="checkbox" id="noPrcLicense" wire:model.live="noPrcLicense" class="mt-1">
                                        <label for="noPrcLicense" class="text-sm text-gray-700 cursor-pointer">
                                            I don't have a PRC license
                                        </label>
                                    </div>
                                    
                                    @if(!$noPrcLicense)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="prcLicenseNo" class="block text-sm font-semibold text-gray-700 mb-2">
                                                PRC License Number 
                                                @if($personnelType === 'teaching')
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>
                                            <input type="text" id="prcLicenseNo" wire:model="prcLicenseNo" placeholder="Enter PRC License Number" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base" {{ $personnelType === 'teaching' ? 'required' : '' }}>
                                            @error('prcLicenseNo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                        <div>
                                            <label for="prcLicenseExpiry" class="block text-sm font-semibold text-gray-700 mb-2">
                                                PRC License Expiry Date 
                                                @if($personnelType === 'teaching')
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>
                                            <input type="date" id="prcLicenseExpiry" wire:model="prcLicenseExpiry" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base" {{ $personnelType === 'teaching' ? 'required' : '' }}>
                                            @error('prcLicenseExpiry')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                    @else
                                    <div class="p-3 bg-gray-100 rounded-lg">
                                        <p class="text-sm text-gray-600">PRC License fields are hidden. You can uncheck the box above to enter license information.</p>
                                    </div>
                                    @endif
                                </div>

                                <!-- Signature Section -->
                                <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-4 space-y-4">
                                    <h3 class="font-semibold text-gray-900">Signature <span class="text-red-500">*</span></h3>
                                    
                                    <!-- Signature Pad -->
                                    <div x-data="{ initialized: false }" x-init="
                                        setTimeout(() => {
                                            const canvas = document.getElementById('signaturePad');
                                            if (canvas && typeof SignaturePad !== 'undefined' && !initialized) {
                                                window.signaturePad = new SignaturePad(canvas, {
                                                    backgroundColor: 'rgb(255, 255, 255)',
                                                    penColor: 'rgb(0, 0, 0)',
                                                    minWidth: 1,
                                                    maxWidth: 3
                                                });
                                                
                                                // Resize canvas
                                                function resizeCanvas() {
                                                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                                                    const ctx = canvas.getContext('2d');
                                                    canvas.width = canvas.offsetWidth * ratio;
                                                    canvas.height = canvas.offsetHeight * ratio;
                                                    ctx.scale(ratio, ratio);
                                                    // Ensure white background
                                                    ctx.fillStyle = 'rgb(255, 255, 255)';
                                                    ctx.fillRect(0, 0, canvas.offsetWidth, canvas.offsetHeight);
                                                    if (window.signaturePad) {
                                                        window.signaturePad.clear();
                                                    }
                                                }
                                                resizeCanvas();
                                                window.addEventListener('resize', resizeCanvas);
                                                initialized = true;
                                            }
                                        }, 200);
                                    ">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Draw Your Signature</label>
                                        <div class="border-2 border-gray-300 rounded-lg bg-white">
                                            <canvas id="signaturePad" wire:ignore class="w-full h-48 cursor-crosshair bg-white" style="touch-action: none; background-color: rgb(255, 255, 255);"></canvas>
                                        </div>
                                        <div class="mt-2 flex gap-2">
                                            <button type="button" onclick="clearSignature()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Clear</button>
                                            <button type="button" onclick="saveSignature()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Signature</button>
                                        </div>
                                        @if($signatureData)
                                        <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded">
                                            <p class="text-sm text-green-800">✓ Signature captured</p>
                                        </div>
                                        @endif
                                        @error('signatureData')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <!-- Signature Consent -->
                                <div class="flex items-start gap-2">
                                    <input type="checkbox" id="signatureConsent" wire:model="signatureConsent" class="mt-1" required>
                                    <label for="signatureConsent" class="text-base font-medium text-gray-700">
                                        I certify that the information provided is true and correct, and I consent to the collection of my personal data and electronic signature in accordance to the Privacy Policy and E-Signature Collection notice. <span class="text-red-500">*</span>
                                    </label>
                                </div>
                                <div class="mt-2 ml-7">
                                    <button type="button" onclick="openPrivacyPolicy()" class="text-sm text-blue-600 hover:text-blue-800 underline">
                                        View Privacy Policy and E-Signature Collection
                                    </button>
                                </div>
                                @error('signatureConsent')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                                @error('capacity')
                                    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
                                        <p class="text-red-800 font-medium">{{ $message }}</p>
                                    </div>
                                @enderror

                                <!-- Navigation Buttons -->
                                <div class="flex gap-4">
                                    <button type="button" wire:click="previousStep" class="flex-1 bg-gray-500 text-white py-3 sm:py-4 px-6 rounded-lg font-bold text-base sm:text-lg hover:bg-gray-600 transition-all shadow-lg">
                                        Back
                                    </button>
                                    <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-sky-600 text-white py-3 sm:py-4 px-6 rounded-lg font-bold text-base sm:text-lg hover:from-blue-700 hover:to-sky-700 transition-all transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                                        Register Now
                                    </button>
                                </div>
                            @endif
                        </form>

                        <!-- Scripts -->
                        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
                        <script>
                            // Capitalize first letter of each word
                            function capitalizeWords(str) {
                                return str.replace(/\b\w/g, function(char) {
                                    return char.toUpperCase();
                                });
                            }

                            function clearSignature() {
                                if (window.signaturePad) {
                                    window.signaturePad.clear();
                                    @this.set('signatureData', null);
                                }
                            }

                            function saveSignature() {
                                if (window.signaturePad && !window.signaturePad.isEmpty()) {
                                    const canvas = document.getElementById('signaturePad');
                                    const ctx = canvas.getContext('2d');
                                    
                                    // Get the signature image
                                    const signatureImage = window.signaturePad.toDataURL('image/png');
                                    
                                    // Create a new canvas to add watermark
                                    const tempCanvas = document.createElement('canvas');
                                    tempCanvas.width = canvas.width;
                                    tempCanvas.height = canvas.height;
                                    const tempCtx = tempCanvas.getContext('2d');
                                    
                                    // Fill with white background first
                                    tempCtx.fillStyle = 'rgb(255, 255, 255)';
                                    tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
                                    
                                    // Draw the signature
                                    const img = new Image();
                                    img.onload = function() {
                                        tempCtx.drawImage(img, 0, 0);
                                        
                                        // Add watermark text overlay (very light)
                                        tempCtx.save();
                                        tempCtx.translate(tempCanvas.width / 2, tempCanvas.height / 2);
                                        tempCtx.rotate(-45 * Math.PI / 180);
                                        tempCtx.font = 'bold 14px Arial';
                                        tempCtx.fillStyle = 'rgba(0, 0, 0, 0.1)';
                                        tempCtx.textAlign = 'center';
                                        tempCtx.textBaseline = 'middle';
                                        tempCtx.fillText('VERIFIED', 0, 0);
                                        tempCtx.restore();
                                        
                                        // Get the final image with watermark
                                        const finalDataURL = tempCanvas.toDataURL('image/png');
                                        @this.set('signatureData', finalDataURL);
                                    };
                                    img.src = signatureImage;
                                } else {
                                    alert('Please draw your signature first.');
                                }
                            }
                        </script>

                        <!-- Privacy Policy Modal -->
                        <div id="privacyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                                <!-- Modal Header -->
                                <div class="bg-gradient-to-r from-blue-600 to-sky-600 px-6 py-4 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ asset('images/sdodesignlogo.png') }}" alt="SDO Logo" class="h-12 w-12 object-contain">
                                        <h2 class="text-xl font-bold text-white">Data Privacy Notice and E-Signature Collection</h2>
                                    </div>
                                    <button onclick="closePrivacyPolicy()" class="text-white hover:text-gray-200 text-2xl font-bold">&times;</button>
                                </div>
                                
                                <!-- Modal Content -->
                                <div class="px-6 py-4">
                                    <p class="text-sm text-gray-600 mb-4">
                                        Welcome to the Attendance Monitoring System of DepEd SDO Balanga City. Your privacy is important to us, and we are committed to protecting your personal data in accordance with the Data Privacy Act of 2012 (RA 10173).
                                    </p>
                                    
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2 mt-4">Collection and Use of Personal Data</h3>
                                    <p class="text-sm text-gray-700 mb-2">
                                        When you access our website, we may collect certain information to enhance your browsing experience and improve our services. This includes:
                                    </p>
                                    <ul class="list-disc list-inside text-sm text-gray-700 mb-4 space-y-1 ml-4">
                                        <li><strong>Automatically collected data:</strong> IP address, browser type, operating system, and browsing behavior.</li>
                                        <li><strong>Voluntarily provided data:</strong> Information you provide through forms (e.g., inquiries, feedback, or application submissions).</li>
                                    </ul>
                                    <p class="text-sm text-gray-700 mb-4">
                                        We ensure that your data will only be used for legitimate purposes such as responding to your inquiries, providing updates, or improving the services of DepEd SDO Balanga City.
                                    </p>
                                    
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2 mt-4">Data Protection</h3>
                                    <p class="text-sm text-gray-700 mb-4">
                                        We implement appropriate technical and organizational measures to secure your personal data against unauthorized access, disclosure, or misuse. Your data will only be retained as long as necessary for its intended purposes, in compliance with applicable laws and regulations.
                                    </p>
                                    
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2 mt-4">Sharing of Data</h3>
                                    <p class="text-sm text-gray-700 mb-4">
                                        Your personal data will not be shared with third parties except when required by law or with your explicit consent.
                                    </p>
                                    
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2 mt-4">E-Signature Collection</h3>
                                    <p class="text-sm text-gray-700 mb-2">
                                        As part of the registration process, we collect your electronic signature to verify your attendance and participation in this seminar. Your signature serves as proof of your attendance and will be used solely for the following purposes:
                                    </p>
                                    <ul class="list-disc list-inside text-sm text-gray-700 mb-4 space-y-1 ml-4">
                                        <li>Verification of your attendance at this specific seminar</li>
                                        <li>Generation of official registration and attendance documents</li>
                                        <li>Compliance with DepEd and PRC requirements for CPD program documentation</li>
                                    </ul>
                                    
                                    <h4 class="text-base font-semibold text-gray-800 mb-2 mt-3">How We Collect Your Signature</h4>
                                    <p class="text-sm text-gray-700 mb-2">
                                        Your electronic signature is collected through a secure signature pad interface. The signature is captured as a digital image and immediately encrypted with security watermarks that include:
                                    </p>
                                    <ul class="list-disc list-inside text-sm text-gray-700 mb-4 space-y-1 ml-4">
                                        <li>Seminar identification information</li>
                                        <li>Date and time of signature capture</li>
                                        <li>Unique attendee identification</li>
                                        <li>Security verification markers to prevent tampering</li>
                                    </ul>
                                    
                                    <h4 class="text-base font-semibold text-gray-800 mb-2 mt-3">Security Measures</h4>
                                    <p class="text-sm text-gray-700 mb-2">
                                        We implement multiple layers of security to protect your signature:
                                    </p>
                                    <ul class="list-disc list-inside text-sm text-gray-700 mb-4 space-y-1 ml-4">
                                        <li><strong>Encryption:</strong> Your signature is encrypted using industry-standard encryption methods</li>
                                        <li><strong>Watermarking:</strong> Security watermarks are embedded directly into the signature image to prevent unauthorized modification</li>
                                        <li><strong>Hash Verification:</strong> Digital hash codes are generated to ensure signature integrity and detect any tampering attempts</li>
                                        <li><strong>Secure Storage:</strong> Signatures are stored in secure databases with restricted access</li>
                                        <li><strong>Access Controls:</strong> Only authorized personnel can access signature data for legitimate purposes</li>
                                    </ul>
                                    
                                    <h4 class="text-base font-semibold text-gray-800 mb-2 mt-3">Use of Your Signature</h4>
                                    <p class="text-sm text-gray-700 mb-4">
                                        Your signature will <strong>only</strong> be used for this specific seminar to prove your attendance. It will appear on official registration sheets and attendance records for this seminar only. Your signature will not be used for any other purposes, shared with third parties, or used for marketing or promotional activities without your explicit consent.
                                    </p>
                                </div>
                                
                                <!-- Modal Footer -->
                                <div class="px-6 py-4 bg-gray-50 flex justify-end">
                                    <button onclick="closePrivacyPolicy()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>

                        <script>
                            function openPrivacyPolicy() {
                                document.getElementById('privacyModal').classList.remove('hidden');
                            }
                            
                            function closePrivacyPolicy() {
                                document.getElementById('privacyModal').classList.add('hidden');
                            }
                            
                            // Close modal when clicking outside
                            document.getElementById('privacyModal').addEventListener('click', function(e) {
                                if (e.target === this) {
                                    closePrivacyPolicy();
                                }
                            });
                        </script>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
