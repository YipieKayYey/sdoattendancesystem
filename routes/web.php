<?php

use App\Livewire\RegisterAttendee;
use App\Models\Attendee;
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
