<?php

namespace App\Services;

use App\Models\Seminar;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class AttendanceCsvService
{
    /**
     * Generate Attendance CSV file
     */
    public function generateAttendanceCsv(Seminar $seminar, ?string $attendeeIds = null): StreamedResponse
    {
        $query = $seminar->attendees()
            ->with(['checkIns.seminarDay'])
            ->orderByRaw("COALESCE(last_name, name) ASC") // Alphabetical by last name
            ->orderByRaw("COALESCE(first_name, '') ASC") // Then by first name
            ->orderBy('created_at', 'asc'); // Then by registration date
        
        if ($attendeeIds) {
            $ids = explode(',', $attendeeIds);
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }
        }
        
        $attendees = $query->get();

        $filename = sprintf(
            'Attendance-%s-%s.csv',
            Str::slug($seminar->title),
            now()->format('Y-m-d')
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($seminar, $attendees) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 to help Excel display special characters correctly
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Check if multi-day
            $isMultiDay = $seminar->isMultiDay();
            $days = $isMultiDay ? $seminar->days()->orderBy('day_number')->get() : collect();
            
            // Days for date/venue columns: multi-day uses actual days; single-day uses Day 1 or seminar
            $day1 = $seminar->days()->orderBy('day_number')->first();
            $daysForDateVenue = $days->isNotEmpty()
                ? $days
                : collect([$day1 ?? (object)['day_number' => 1, 'date' => $seminar->date, 'venue' => $seminar->venue ?? 'N/A']]);
            
            // Build headers
            $headers = [
                'No.',
                'Full Name',
                'Position',
            ];
            
            if ($isMultiDay) {
                foreach ($days as $day) {
                    $headers[] = "Day {$day->day_number} Check-in";
                    $headers[] = "Day {$day->day_number} Check-out";
                }
            } else {
                $headers[] = 'Checked In At';
                $headers[] = 'Checked Out At';
            }
            
            $headers = array_merge($headers, [
                'Registered At',
                'First Name',
                'Middle Name',
                'Last Name',
                'Suffix',
                'Sex',
                'School/Office/Agency',
                'Email',
                'Mobile Phone',
                'Personnel Type',
                'PRC License No.',
                'PRC License Expiry',
                'Signature Status',
                'Signed At',
                'Ticket Hash',
                'Seminar Title',
            ]);
            
            foreach ($daysForDateVenue as $day) {
                $headers[] = "Seminar Date Day {$day->day_number}";
                $headers[] = "Seminar Venue Day {$day->day_number}";
            }
            
            fputcsv($file, $headers);

            // CSV Data Rows
            $number = 1;
            foreach ($attendees as $attendee) {
                $fullName = $attendee->full_name ?: $attendee->name ?: 'N/A';
                $personnelType = $attendee->personnel_type === 'teaching' ? 'Teaching' : 
                                ($attendee->personnel_type === 'non_teaching' ? 'Non-Teaching' : 'N/A');
                
                $prcLicenseNo = $attendee->isTeaching() ? ($attendee->prc_license_no ?? 'N/A') : 'N/A';
                $prcExpiry = $attendee->isTeaching() && $attendee->prc_license_expiry 
                    ? $attendee->prc_license_expiry->format('Y-m-d') 
                    : 'N/A';
                
                // Build row data
                $row = [
                    $number++,
                    $fullName,
                    $attendee->position ?: 'N/A',
                ];
                
                if ($isMultiDay) {
                    // Add per-day check-in/out data
                    foreach ($days as $day) {
                        $checkIn = $attendee->getCheckInForDay($day->id);
                        $row[] = $checkIn && $checkIn->checked_in_at 
                            ? $checkIn->checked_in_at->format('Y-m-d H:i:s') 
                            : 'Not checked in';
                        $row[] = $checkIn && $checkIn->checked_out_at 
                            ? $checkIn->checked_out_at->format('Y-m-d H:i:s') 
                            : 'Not checked out';
                    }
                } else {
                    // Single day
                    $row[] = $attendee->checked_in_at 
                        ? $attendee->checked_in_at->format('Y-m-d H:i:s') 
                        : 'Not checked in';
                    $row[] = $attendee->checked_out_at 
                        ? $attendee->checked_out_at->format('Y-m-d H:i:s') 
                        : 'Not checked out';
                }
                
                $signatureStatus = $attendee->hasSignature() ? 'Yes' : 'No';
                $signedAt = $attendee->signature_timestamp 
                    ? $attendee->signature_timestamp->format('Y-m-d H:i:s') 
                    : 'N/A';
                
                $sex = $attendee->sex ? ucfirst($attendee->sex) : 'N/A';
                $schoolOfficeAgency = $attendee->school_office_agency_display !== '—' ? $attendee->school_office_agency_display : 'N/A';
                $registeredAt = $attendee->created_at 
                    ? $attendee->created_at->format('Y-m-d H:i:s') 
                    : 'N/A';
                
                $row = array_merge($row, [
                    $registeredAt,
                    $attendee->first_name ?: 'N/A',
                    $attendee->middle_name ?: 'N/A',
                    $attendee->last_name ?: 'N/A',
                    $attendee->suffix ?: 'N/A',
                    $sex,
                    $schoolOfficeAgency,
                    $attendee->email ?: 'N/A',
                    $attendee->mobile_phone ?: 'N/A',
                    $personnelType,
                    $prcLicenseNo,
                    $prcExpiry,
                    $signatureStatus,
                    $signedAt,
                    $attendee->ticket_hash ?: 'N/A',
                    $seminar->title ?: 'N/A',
                ]);
                
                foreach ($daysForDateVenue as $d) {
                    $row[] = $d->date ? \Carbon\Carbon::parse($d->date)->format('Y-m-d') : 'N/A';
                    $row[] = $d->venue ?? 'N/A';
                }
                
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
