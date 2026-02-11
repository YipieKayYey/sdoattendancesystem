<?php

use App\Livewire\RegisterAttendee;
use App\Models\Attendee;
use App\Models\Seminar;
use App\Services\RegistrationSheetPdfService;
use App\Services\AttendanceSheetPdfService;
use App\Services\AttendanceCsvService;
use App\Services\AnalyticsPdfService;
use App\Services\AnalyticsCsvService;
use App\Http\Controllers\RegistrationDetailsController;
use Illuminate\Support\Facades\Route;
use Milon\Barcode\DNS2D;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', function () {
    return view('welcome');
});

// Redirect to Filament admin login
Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::get('/register/{slug}', RegisterAttendee::class)
    ->name('register');

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

// PDF Export Routes (Protected by Filament auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/seminars/{seminar}/export-registration-sheet', function (Seminar $seminar) {
        $service = app(RegistrationSheetPdfService::class);
        $attendeeIds = request()->query('attendee_ids');
        $blankSignatures = request()->query('blank_signatures') === '1';
        return $service->generateRegistrationSheet($seminar, $attendeeIds, $blankSignatures);
    })->name('seminars.export-registration-sheet');

    Route::get('/admin/seminars/{seminar}/export-attendance-sheet', function (Seminar $seminar) {
        $service = app(AttendanceSheetPdfService::class);
        $attendeeIds = request()->query('attendee_ids');
        $blankSignatures = request()->query('blank_signatures') === '1';
        return $service->generateAttendanceSheet($seminar, $attendeeIds, $blankSignatures);
    })->name('seminars.export-attendance-sheet');

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
});
