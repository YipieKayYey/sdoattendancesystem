<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GNR Attendance Sheet - {{ $seminar->title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { margin: 0; size: 8.5in 13in; }
        body { font-family: "Liberation Sans", Arial, sans-serif; font-size: 11pt; margin: 24pt 48pt 12pt 48pt; }
        .main-title { text-align: center; font-size: 12pt; font-weight: bold; margin-bottom: 4pt; }
        .event-details { text-align: center; font-size: 10pt; margin-bottom: 12pt; }
        .section-heading { text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 8pt; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { border: 1pt solid #000; padding: 8pt 6pt; font-size: 10pt; }
        table th { font-weight: bold; text-align: center; }
        .signature-cell { min-width: 90pt; min-height: 36pt; }
        .signature-container { display: inline-block; width: 120px; height: 36px; overflow: hidden; position: relative; }
        .signature-image { max-width: 120px; max-height: 36px; display: block; opacity: 0.7; }
    </style>
</head>
<body>
@php
    $number = 1;
@endphp

@if($attendees->count() > 0)
    <div class="main-title">{{ strtoupper($seminar->title) }}</div>

    @php
        $displayDate = ($day ?? null) ? $day->date->format('F j, Y') : $seminar->date->format('F j, Y');
        $displayVenue = ($day ?? null) ? ($day->venue ?? 'N/A') : ($seminar->venue ?? 'N/A');
    @endphp
    <div class="event-details">{{ $displayDate }} / {{ $displayVenue }}</div>
    <div class="section-heading">ATTENDANCE{{ ($day ?? null) ? ' DAY ' . $day->day_number : '' }}</div>

    <table>
        <thead>
            <tr>
                <th width="6%">No.</th>
                <th width="35%">Name</th>
                <th width="6%">Sex</th>
                <th width="22%">Position</th>
                <th width="22%">Office/Unit</th>
                <th class="signature-cell" width="15%">Signature</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendees as $attendee)
                <tr>
                    <td style="text-align: center;">{{ $number++ }}</td>
                    <td style="word-wrap: break-word;">{{ $attendee->full_name ?? $attendee->name }}</td>
                    <td style="text-align: center;">@php $s = $attendee->sex ?? null; echo $s === 'male' ? 'M' : ($s === 'female' ? 'F' : '—'); @endphp</td>
                    <td style="word-wrap: break-word;">{{ $attendee->position ?? '—' }}</td>
                    <td style="word-wrap: break-word;">{{ $attendee->school_office_agency ?? '—' }}</td>
                    <td class="signature-cell" style="text-align: center;">
                        @if (!($blankSignatures ?? false) && $attendee->hasSignature() && $attendee->signature_image)
                            <div class="signature-container">
                                <img src="data:image/png;base64,{{ $attendee->signature_image }}" alt="Signature" class="signature-image">
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="main-title">{{ strtoupper($seminar->title) }}</div>
    @php
        $displayDate = ($day ?? null) ? $day->date->format('F j, Y') : $seminar->date->format('F j, Y');
        $displayVenue = ($day ?? null) ? ($day->venue ?? 'N/A') : ($seminar->venue ?? 'N/A');
    @endphp
    <div class="event-details">{{ $displayDate }} / {{ $displayVenue }}</div>
    <div class="section-heading">ATTENDANCE{{ ($day ?? null) ? ' DAY ' . $day->day_number : '' }}</div>
    <p style="margin-top: 20pt;">No checked-in attendees found.</p>
@endif
</body>
</html>
