<div class="flex flex-col items-center justify-center w-full">
    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Registration QR Code</h3>
    <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg border-2 border-blue-200 dark:border-blue-700 shadow-sm flex justify-center w-full">
        <div class="flex justify-center">
            <img src="{{ route('seminars.registration-qr', $seminar) }}" alt="Registration QR Code" class="w-40 h-40 sm:w-48 sm:h-48 object-contain mx-auto">
        </div>
    </div>
    <div class="flex justify-center gap-3 mt-4">
        <a href="{{ route('seminars.registration-qr.view', $seminar) }}" target="_blank"
           class="inline-flex items-center justify-center gap-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            View
        </a>
        <a href="{{ route('seminars.registration-qr.download', $seminar) }}" download
           class="inline-flex items-center justify-center gap-1 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Download
        </a>
    </div>
</div>
