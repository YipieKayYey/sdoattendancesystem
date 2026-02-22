<?php

namespace App\Http\Controllers;

use App\Services\UniversalQrPdfService;

class UniversalQrPdfController extends Controller
{
    public function __construct(
        private UniversalQrPdfService $pdfService
    ) {}

    public function preview()
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'attendee') {
            abort(403);
        }

        $profile = $user->attendeeProfile;
        if (!$profile) {
            abort(404, 'No attendee profile found.');
        }

        return $this->pdfService->stream($profile);
    }

    public function download()
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'attendee') {
            abort(403);
        }

        $profile = $user->attendeeProfile;
        if (!$profile) {
            abort(404, 'No attendee profile found.');
        }

        return $this->pdfService->download($profile);
    }
}
