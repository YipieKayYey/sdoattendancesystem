<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration QR Code - {{ $seminar->title }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-sky-100">
    <div class="min-h-screen py-4 sm:py-8 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
                <!-- Header Section with Logo -->
                <div class="bg-gradient-to-r from-blue-600 to-sky-600 px-4 sm:px-8 py-4 sm:py-6 text-white">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                        <div class="flex-shrink-0">
                            <img src="{{ asset('images/sdodesignlogo.png') }}" alt="SDO Logo" class="h-16 sm:h-20 w-16 sm:w-20 object-contain">
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-2xl sm:text-3xl font-bold mb-2 break-words">{{ $seminar->title }}</h1>
                            <p class="text-blue-100 text-sm sm:text-base">Registration QR Code</p>
                        </div>
                    </div>
                </div>

                <!-- QR Code Section -->
                <div class="px-4 sm:px-8 py-4 sm:py-6">
                    <div class="text-center">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">Scan to Register</h3>
                        <div class="bg-white p-4 sm:p-6 rounded-lg border-2 border-blue-200 inline-block shadow-sm">
                            <div class="flex justify-center">
                                <img src="{{ route('seminars.registration-qr', $seminar) }}" alt="Registration QR Code" class="w-48 h-48 sm:w-64 sm:h-64 object-contain">
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm sm:text-base mt-4 max-w-md mx-auto">Share this QR code so attendees can scan and register for this seminar.</p>
                        <a href="{{ route('seminars.registration-qr.download', $seminar) }}" download
                           class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors mt-6">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download QR Code
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
