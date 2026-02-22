<?php

namespace App\Services;

use App\Models\AttendeeProfile;
use Barryvdh\DomPDF\Facade\Pdf;

class UniversalQrPdfService
{
    public function generatePdf(AttendeeProfile $profile)
    {
        $profile->load(['user', 'school']);

        $data = [
            'profile' => $profile,
            'generatedAt' => now(),
        ];

        return Pdf::loadView('pdf.universal-qr-details', $data);
    }

    public function download(AttendeeProfile $profile)
    {
        $pdf = $this->generatePdf($profile);
        $filename = 'universal-qr-' . $profile->universal_qr_hash . '.pdf';

        return $pdf->download($filename);
    }

    public function stream(AttendeeProfile $profile)
    {
        $pdf = $this->generatePdf($profile);

        return $pdf->stream('universal-qr.pdf');
    }
}
