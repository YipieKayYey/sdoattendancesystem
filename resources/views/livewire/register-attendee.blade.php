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
                            <h1 class="text-2xl sm:text-3xl font-bold mb-2 break-words">{{ $seminar->title }}</h1>
                            <div class="flex items-center justify-center sm:justify-start gap-2 sm:gap-4 text-blue-100">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium text-sm sm:text-base">{{ $seminar->date->format('F j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Section -->
                <div class="px-4 sm:px-8 py-4 sm:py-6">
                    @if($seminar->isFull())
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
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <span class="text-gray-700 font-medium text-sm sm:text-base">Registration Status</span>
                                <span class="text-blue-700 font-bold text-base sm:text-lg">
                                    {{ $seminar->registered_count }} of 
                                    @if($seminar->is_open)
                                        <span class="text-blue-600">Unlimited</span>
                                    @else
                                        {{ $seminar->capacity }}
                                    @endif
                                    spots filled
                                </span>
                            </div>
                        </div>
                        
                        <!-- Registration Form -->
                        <form wire:submit="register" class="space-y-5">
                            <!-- Name Field -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="name"
                                    wire:model="name"
                                    placeholder="Enter your full name"
                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                                    required
                                >
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Email Field -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="email"
                                    wire:model="email"
                                    placeholder="Enter your email address"
                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                                    required
                                >
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Position Field -->
<div>
                                <label for="position" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Position / Job Title
                                </label>
                                <input 
                                    type="text" 
                                    id="position"
                                    wire:model="position"
                                    placeholder="Enter your position or job title (optional)"
                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm sm:text-base"
                                >
                                @error('position')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            @error('capacity')
                                <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
                                    <p class="text-red-800 font-medium">{{ $message }}</p>
                                </div>
                            @enderror

                            <!-- Submit Button -->
                            <button 
                                type="submit"
                                class="w-full bg-gradient-to-r from-blue-600 to-sky-600 text-white py-3 sm:py-4 px-6 rounded-lg font-bold text-base sm:text-lg hover:from-blue-700 hover:to-sky-700 transition-all transform hover:scale-[1.02] shadow-lg hover:shadow-xl"
                            >
                                Register Now
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
