<?php

namespace App\Services;

use App\Models\Seminar;
use Mpdf\Mpdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Alternative PDF service using mPDF
 * Better CSS support than DomPDF
 */
class MpdfRegistrationSheetService
{
    public function generateRegistrationSheet(Seminar $seminar): Response
    {
        $attendees = $seminar->attendees()->orderBy('created_at')->get();
        
        if ($attendees->isEmpty()) {
            abort(404, 'No attendees found for this seminar.');
        }

        $html = view('pdf.registration-sheet-mpdf', [
            'seminar' => $seminar,
            'attendees' => $attendees,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L', // Landscape
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'orientation' => 'L',
            'tempDir' => storage_path('app/tmp'),
        ]);

        $mpdf->WriteHTML($html);
        
        $filename = sprintf(
            'Registration-Sheet-%s-%s.pdf',
            Str::slug($seminar->title),
            now()->format('Y-m-d')
        );

        return response($mpdf->Output($filename, 'D'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
