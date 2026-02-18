<?php

namespace App\Services;

use App\Models\Attendee;
use App\Models\Seminar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class RegistrationSheetPdfService
{
    /**
     * Generate Registration Sheet PDF using HTML template
     *
     * @param Seminar $seminar
     * @param string|null $attendeeIds Comma-separated list of attendee IDs
     * @param bool $blankSignatures Whether to leave signature column blank
     * @param int|null $dayId For multi-day: which day's date/venue to show in header
     */
    public function generateRegistrationSheet(Seminar $seminar, ?string $attendeeIds = null, bool $blankSignatures = false, ?int $dayId = null): Response
    {
        $day = null;
        if ($seminar->isMultiDay() && $dayId) {
            $day = $seminar->days()->find($dayId);
        }

        $query = $seminar->attendees()
            ->orderByRaw('COALESCE(NULLIF(last_name, ""), name) ASC')
            ->orderBy('first_name');
        
        if ($attendeeIds) {
            $ids = explode(',', $attendeeIds);
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }
        }
        
        $attendees = $query->get();
        
        if ($attendees->isEmpty()) {
            abort(404, 'No attendees found for this seminar.');
        }

        $pdf = Pdf::loadView('pdf.registration-sheet', [
            'seminar' => $seminar,
            'attendees' => $attendees,
            'blankSignatures' => $blankSignatures,
            'day' => $day,
        ])
        ->setPaper([0, 0, 1008, 612], 'landscape') // 14in x 8.5in in points (14*72 = 1008, 8.5*72 = 612)
        ->setOption('enable-local-file-access', true)
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);
        
        
        $filename = sprintf(
            'Registration-Sheet-%s-%s.pdf',
            Str::slug($seminar->title),
            now()->format('Y-m-d')
        );

        return $pdf->stream($filename);
    }
}
