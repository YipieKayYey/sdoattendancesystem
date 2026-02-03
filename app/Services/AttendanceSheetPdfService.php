<?php

namespace App\Services;

use App\Models\Attendee;
use App\Models\Seminar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class AttendanceSheetPdfService
{
    /**
     * Generate Attendance Sheet PDF using HTML template
     */
    public function generateAttendanceSheet(Seminar $seminar, ?string $attendeeIds = null): Response
    {
        $query = $seminar->attendees()
            ->whereNotNull('checked_in_at')
            ->orderBy('checked_in_at');
        
        if ($attendeeIds) {
            $ids = explode(',', $attendeeIds);
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }
        }
        
        $attendees = $query->get();
        
        if ($attendees->isEmpty()) {
            abort(404, 'No checked-in attendees found for this seminar.');
        }

        $pdf = Pdf::loadView('pdf.attendance-sheet', [
            'seminar' => $seminar,
            'attendees' => $attendees,
        ])
        ->setPaper([0, 0, 612, 936], 'portrait') // 8.5in x 13in in points (8.5*72 = 612, 13*72 = 936)
        ->setOption('enable-local-file-access', true)
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);
        
        $filename = sprintf(
            'Attendance-Sheet-%s-%s.pdf',
            Str::slug($seminar->title),
            now()->format('Y-m-d')
        );

        return $pdf->stream($filename);
    }
}
