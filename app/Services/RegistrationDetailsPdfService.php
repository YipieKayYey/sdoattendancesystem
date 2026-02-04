<?php

namespace App\Services;

use App\Models\Attendee;
use Barryvdh\DomPDF\Facade\Pdf;

class RegistrationDetailsPdfService
{
    public function generateRegistrationDetailsPdf(Attendee $attendee)
    {
        // Eager load relationships to ensure data is available
        $attendee->load(['seminar', 'seminar.days']);
        
        $data = [
            'attendee' => $attendee,
            'seminar' => $attendee->seminar,
            'generatedAt' => now(),
        ];

        $pdf = Pdf::loadView('pdf.registration-details', $data);
        
        return $pdf;
    }

    public function downloadRegistrationDetailsPdf(Attendee $attendee)
    {
        $pdf = $this->generateRegistrationDetailsPdf($attendee);
        
        $filename = 'registration-details-' . $attendee->ticket_hash . '.pdf';
        
        return $pdf->download($filename);
    }

    public function streamRegistrationDetailsPdf(Attendee $attendee)
    {
        $pdf = $this->generateRegistrationDetailsPdf($attendee);
        
        return $pdf->stream('registration-details.pdf');
    }
}
