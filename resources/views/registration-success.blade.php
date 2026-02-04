<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-sky-100">
    <div class="min-h-screen py-4 sm:py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <!-- Success Card -->
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                <!-- Header Section with Logo and Success Message -->
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-4 sm:px-8 py-4 sm:py-6 text-white">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <img src="{{ asset('images/sdodesignlogo.png') }}" alt="SDO Logo" class="h-16 sm:h-20 w-16 sm:w-20 object-contain">
                        </div>
                        <!-- Success Message -->
                        <div class="flex-1 text-center sm:text-left">
                            <div class="flex items-center justify-center sm:justify-start gap-3 mb-2">
                                <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-full">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <h1 class="text-2xl sm:text-3xl font-bold">Registration Successful!</h1>
                            </div>
                            <p class="text-green-100 text-sm sm:text-base">Your QR Code has been generated</p>
                        </div>
                    </div>
                </div>

                <!-- Content Section -->
                <div class="px-4 sm:px-8 py-4 sm:py-6">
                    <!-- Attendee Details Card -->
                    <div class="bg-blue-50 rounded-lg border border-blue-200 p-4 sm:p-6 mb-6">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">Registration Details</h3>
                        <div class="space-y-3 text-gray-700">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 pb-3 border-b border-blue-200 last:border-b-0 last:pb-0">
                                <span class="font-semibold text-sm sm:text-base min-w-[80px] sm:min-w-[100px]">Name:</span>
                                <span class="text-sm sm:text-base break-words">{{ $attendee->full_name ?? $attendee->name }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 pb-3 border-b border-blue-200 last:border-b-0 last:pb-0">
                                <span class="font-semibold text-sm sm:text-base min-w-[80px] sm:min-w-[100px]">Email:</span>
                                <span class="text-sm sm:text-base break-all">{{ $attendee->email }}</span>
                            </div>
                            @if($attendee->mobile_phone)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 pb-3 border-b border-blue-200 last:border-b-0 last:pb-0">
                                <span class="font-semibold text-sm sm:text-base min-w-[80px] sm:min-w-[100px]">Mobile Phone:</span>
                                <span class="text-sm sm:text-base break-words">{{ $attendee->mobile_phone }}</span>
                            </div>
                            @endif
                            @if($attendee->position)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 pb-3 border-b border-blue-200 last:border-b-0 last:pb-0">
                                <span class="font-semibold text-sm sm:text-base min-w-[80px] sm:min-w-[100px]">Position:</span>
                                <span class="text-sm sm:text-base break-words">{{ $attendee->position }}</span>
                            </div>
                            @endif
                            @if($attendee->isTeaching() && $attendee->prc_license_no)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 pb-3 border-b border-blue-200 last:border-b-0 last:pb-0">
                                <span class="font-semibold text-sm sm:text-base min-w-[80px] sm:min-w-[100px]">PRC License No:</span>
                                <span class="text-sm sm:text-base break-words">{{ $attendee->prc_license_no }}</span>
                            </div>
                            @endif
                            @if($attendee->isTeaching() && $attendee->prc_license_expiry)
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 pb-3 border-b border-blue-200 last:border-b-0 last:pb-0">
                                <span class="font-semibold text-sm sm:text-base min-w-[80px] sm:min-w-[100px]">PRC Expiry Date:</span>
                                <span class="text-sm sm:text-base break-words">{{ $attendee->prc_license_expiry->format('F j, Y') }}</span>
                            </div>
                            @endif
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 pb-3 border-b border-blue-200 last:border-b-0 last:pb-0">
                                <span class="font-semibold text-sm sm:text-base min-w-[80px] sm:min-w-[100px]">Ticket Code:</span>
                                <code class="bg-white px-2 sm:px-3 py-1 sm:py-1.5 rounded text-xs sm:text-sm font-mono border border-blue-300 text-blue-700 break-all">{{ $attendee->ticket_hash }}</code>
                            </div>
                            @if($attendee->hasSignature())
                            <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-2 pb-3 border-b border-blue-200 last:border-b-0 last:pb-0">
                                <span class="font-semibold text-sm sm:text-base min-w-[80px] sm:min-w-[100px]">Signature:</span>
                                <div class="flex-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-2">✓ Captured</span>
                                    @if($attendee->signature_timestamp)
                                    <p class="text-xs text-gray-600 mt-1">Signed on {{ $attendee->signature_timestamp->format('F j, Y g:i A') }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="text-center">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">Your Ticket QR Code</h3>
                        <div class="bg-white p-4 sm:p-6 rounded-lg border-2 border-blue-200 inline-block shadow-sm">
                            <div class="flex justify-center">
                                {!! $barcodeImage !!}
                            </div>
                        </div>
                        
                        <!-- Reminder Notice -->
                        <div class="bg-amber-50 border-l-4 border-amber-500 rounded-lg p-4 sm:p-5 mt-4 max-w-lg mx-auto">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div class="text-left">
                                    <p class="font-semibold text-amber-900 text-sm sm:text-base mb-1">Important Reminder</p>
                                    <p class="text-amber-800 text-xs sm:text-sm leading-relaxed">
                                        Please <strong>screenshot or save</strong> this QR code. You will need to present it at the event as it will be scanned by the attendance officer for check-in.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PDF Download Options -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 sm:p-5 mt-4 max-w-lg mx-auto">
                            <div class="text-left">
                                <p class="font-semibold text-blue-900 text-sm sm:text-base mb-3">Download Registration Details</p>
                                <p class="text-blue-800 text-xs sm:text-sm leading-relaxed mb-4">
                                    Get a PDF copy of your registration details for your records.
                                </p>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <a href="{{ route('registration-details.preview', $attendee->ticket_hash) }}" 
                                       target="_blank"
                                       class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Preview PDF
                                    </a>
                                    <a href="{{ route('registration-details.download', $attendee->ticket_hash) }}" 
                                       class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Download PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
