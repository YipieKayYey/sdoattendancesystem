<?php

use App\Livewire\RegisterAttendee;
use App\Models\Attendee;
use App\Models\Seminar;
use App\Services\RegistrationSheetPdfService;
use App\Services\AttendanceSheetPdfService;
use App\Services\AttendanceCsvService;
use App\Services\AnalyticsPdfService;
use App\Services\AnalyticsCsvService;
use App\Services\SeminarQrCodeService;
use App\Http\Controllers\RegistrationDetailsController;
use Illuminate\Support\Facades\Route;
use Milon\Barcode\DNS2D;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', function () {
    return view('welcome');
});

// Redirect /login to homepage; users choose Admin or Attendee from there
Route::get('/login', function () {
    return redirect('/');
})->name('login');

Route::get('/register/{slug}', RegisterAttendee::class)
    ->middleware('throttle:registration')
    ->name('register');

Route::get('/survey/{slug}', function (string $slug) {
    $seminar = \App\Models\Seminar::where('slug', $slug)->first();

    if (!$seminar) {
        abort(404);
    }

    $redirectUrl = $seminar->survey_form_url
        ?: route('register', ['slug' => $slug]);

    if ($seminar->survey_form_url) {
        $cookieName = 'survey_clicked_' . $seminar->id;
        if (!request()->cookie($cookieName)) {
            $seminar->surveyLinkClicks()->create(['clicked_at' => now()]);
            cookie()->queue($cookieName, '1', 60 * 24);
        }
    }

    return redirect()->away($redirectUrl);
})->name('survey.redirect');

Route::get('/registration/success/{ticket_hash}', function (string $ticket_hash) {
    $attendee = Attendee::where('ticket_hash', $ticket_hash)->firstOrFail();
    
    $dns2d = new DNS2D();
    $barcodeImage = $dns2d->getBarcodeHTML($ticket_hash, 'QRCODE', 8, 8);
    
    return view('registration-success', [
        'attendee' => $attendee,
        'barcodeImage' => $barcodeImage,
    ]);
})->name('registration.success');

// Registration Details PDF Routes
Route::get('/registration-details/{ticket_hash}/preview', [RegistrationDetailsController::class, 'preview'])
    ->name('registration-details.preview');

Route::get('/registration-details/{ticket_hash}/download', [RegistrationDetailsController::class, 'download'])
    ->name('registration-details.download');

Route::get('/ticket/{ticket_hash}/download', function (string $ticket_hash) {
    $attendee = Attendee::where('ticket_hash', $ticket_hash)->firstOrFail();
    
    $dns2d = new DNS2D();
    $barcodeImage = $dns2d->getBarcodeHTML($ticket_hash, 'QRCODE', 8, 8);
    
    $pdf = Pdf::loadView('ticket-pdf', [
        'attendee' => $attendee,
        'barcodeImage' => $barcodeImage,
    ]);
    
    return $pdf->download('ticket-' . $ticket_hash . '.pdf');
})->name('ticket.download');

// Attendee Universal QR PDF routes (attendee auth required)
Route::middleware(['auth'])->group(function () {
    Route::get('/attendee-qr/preview', [App\Http\Controllers\UniversalQrPdfController::class, 'preview'])
        ->name('attendee.universal-qr.preview');
    Route::get('/attendee-qr/download', [App\Http\Controllers\UniversalQrPdfController::class, 'download'])
        ->name('attendee.universal-qr.download');
});

// Admin routes (Protected by auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/seminars/{id}/live-dashboard', App\Livewire\SeminarLiveDashboard::class)
        ->name('admin.seminars.live-dashboard')
        ->whereNumber('id');
});

// PDF Export Routes (Protected by Filament auth middleware + rate limit)
Route::middleware(['auth', 'throttle:exports'])->group(function () {
    Route::get('/admin/seminars/{seminar}/export-registration-sheet', function (Seminar $seminar) {
        $service = app(RegistrationSheetPdfService::class);
        $attendeeIds = request()->query('attendee_ids');
        $blankSignatures = request()->query('blank_signatures') === '1';
        $dayId = request()->query('day_id');
        return $service->generateRegistrationSheet($seminar, $attendeeIds, $blankSignatures, $dayId ? (int) $dayId : null);
    })->name('seminars.export-registration-sheet');

    Route::get('/admin/seminars/{seminar}/export-attendance-sheet', function (Seminar $seminar) {
        $service = app(AttendanceSheetPdfService::class);
        $attendeeIds = request()->query('attendee_ids');
        $blankSignatures = request()->query('blank_signatures') === '1';
        $dayId = request()->query('day_id');
        return $service->generateAttendanceSheet($seminar, $attendeeIds, $blankSignatures, $dayId ? (int) $dayId : null);
    })->name('seminars.export-attendance-sheet');

    Route::get('/admin/seminars/{seminar}/export-gnr-attendance-sheet', function (Seminar $seminar) {
        $service = app(AttendanceSheetPdfService::class);
        $attendeeIds = request()->query('attendee_ids');
        $blankSignatures = request()->query('blank_signatures') === '1';
        $dayId = request()->query('day_id');
        return $service->generateGnrAttendanceSheet($seminar, $attendeeIds, $blankSignatures, $dayId ? (int) $dayId : null);
    })->name('seminars.export-gnr-attendance-sheet');

    Route::get('/admin/seminars/{seminar}/export-attendance-csv', function (Seminar $seminar) {
        $service = app(AttendanceCsvService::class);
        $attendeeIds = request()->query('attendee_ids');
        return $service->generateAttendanceCsv($seminar, $attendeeIds);
    })->name('seminars.export-attendance-csv');

    // Analytics Export Routes
    Route::get('/admin/analytics/{seminar}/export-pdf', function (Seminar $seminar) {
        $service = app(AnalyticsPdfService::class);
        return $service->generateAnalyticsReport($seminar);
    })->name('analytics.export-pdf');

    Route::get('/admin/analytics/{seminar}/export-csv', function (Seminar $seminar) {
        $service = app(AnalyticsCsvService::class);
        return $service->generateAnalyticsCsv($seminar);
    })->name('analytics.export-csv');

    Route::get('/admin/seminars/{seminar}/registration-qr/view', function (Seminar $seminar) {
        return view('seminar-registration-qr-view', ['seminar' => $seminar]);
    })->name('seminars.registration-qr.view');

    Route::get('/admin/seminars/{seminar}/registration-qr', function (Seminar $seminar) {
        ob_start();
        try {
            $service = app(SeminarQrCodeService::class);
            $png = $service->generatePng($seminar);
        } finally {
            ob_end_clean();
        }
        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline; filename="registration-qr-' . $seminar->slug . '.png"');
    })->name('seminars.registration-qr');

    Route::get('/admin/seminars/{seminar}/registration-qr/download', function (Seminar $seminar) {
        ob_start();
        try {
            $service = app(SeminarQrCodeService::class);
            $png = $service->generatePng($seminar);
        } finally {
            ob_end_clean();
        }
        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="registration-qr-' . $seminar->slug . '.png"');
    })->name('seminars.registration-qr.download');
});
