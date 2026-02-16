<?php

namespace App\Services;

use App\Models\Seminar;
use App\Models\SeminarDay;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class AttendanceSheetPdfService
{
    /**
     * Generate Attendance Sheet PDF using HTML template
     *
     * @param bool $blankSignatures Whether to leave signature column blank
     * @param int|null $dayId For multiday seminars, which day to use. Date/time/venue/room follow that day's schedule.
     */
    public function generateAttendanceSheet(Seminar $seminar, ?string $attendeeIds = null, bool $blankSignatures = false, ?int $dayId = null): Response
    {
        $day = null;
        if ($seminar->isMultiDay()) {
            $day = $dayId ? $seminar->days()->find($dayId) : $seminar->days()->first();
        }

        $query = $seminar->attendees()
            ->orderByRaw('COALESCE(NULLIF(last_name, ""), name) ASC')
            ->orderBy('first_name');

        if ($day) {
            $query->whereHas('checkIns', fn ($q) => $q->where('seminar_day_id', $day->id)->whereNotNull('checked_in_at'));
        } else {
            $query->whereNotNull('checked_in_at');
        }

        $ids = $attendeeIds ? array_filter(array_map('intval', explode(',', $attendeeIds))) : [];
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } else {
            $query->whereRaw('1 = 0');
        }

        $attendees = $query->get();

        $pdf = Pdf::loadView('pdf.attendance-sheet', [
            'seminar' => $seminar,
            'attendees' => $attendees,
            'blankSignatures' => $blankSignatures,
            'day' => $day,
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

    /**
     * Generate GNR-style Attendance Sheet PDF (No., Name, Sex, Position, Office/Unit, Signature).
     * Like CPD: admin picks a day for multi-day; sheet shows that day's details + single Signature column.
     */
    public function generateGnrAttendanceSheet(Seminar $seminar, ?string $attendeeIds = null, bool $blankSignatures = false, ?int $dayId = null): Response
    {
        $day = null;
        if ($seminar->isMultiDay()) {
            $day = $dayId ? $seminar->days()->find($dayId) : $seminar->days()->first();
        }

        $query = $seminar->attendees()
            ->orderByRaw('COALESCE(NULLIF(last_name, ""), name) ASC')
            ->orderBy('first_name');

        if ($day) {
            $query->whereHas('checkIns', fn ($q) => $q->where('seminar_day_id', $day->id)->whereNotNull('checked_in_at'));
        } else {
            $query->whereNotNull('checked_in_at');
        }

        $ids = $attendeeIds ? array_filter(array_map('intval', explode(',', $attendeeIds))) : [];
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } else {
            $query->whereRaw('1 = 0');
        }

        $attendees = $query->get();

        $pdf = Pdf::loadView('pdf.gnr-attendance-sheet', [
            'seminar' => $seminar,
            'attendees' => $attendees,
            'blankSignatures' => $blankSignatures,
            'day' => $day,
        ])
        ->setPaper([0, 0, 612, 936], 'portrait')
        ->setOption('enable-local-file-access', true)
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false);

        $filename = sprintf(
            'GNR-Attendance-%s-%s.pdf',
            Str::slug($seminar->title),
            now()->format('Y-m-d')
        );

        return $pdf->stream($filename);
    }
}
