<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Services\RegistrationDetailsPdfService;
use Illuminate\Http\Request;

class RegistrationDetailsController extends Controller
{
    public function __construct(
        private RegistrationDetailsPdfService $pdfService
    ) {}

    public function preview($ticketHash)
    {
        $attendee = Attendee::where('ticket_hash', $ticketHash)->firstOrFail();
        
        // Generate PDF for preview (stream in browser)
        return $this->pdfService->streamRegistrationDetailsPdf($attendee);
    }

    public function download($ticketHash)
    {
        $attendee = Attendee::where('ticket_hash', $ticketHash)->firstOrFail();
        
        // Generate PDF for download
        return $this->pdfService->downloadRegistrationDetailsPdf($attendee);
    }
}
