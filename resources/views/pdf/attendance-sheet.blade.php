<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Sheet - {{ $seminar->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
            size: 8.5in 13in;
        }

        body {
            font-family: "Liberation Sans", Arial, sans-serif;
            font-size: 11pt;
            margin: 24pt 48pt 12pt 48pt;
            padding: 0;
        }

        .page {
            width: 100%;
            display: block;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .header-table td {
            vertical-align: middle;
        }

        .main-title {
            margin-top: 6pt;
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10pt;
            margin-bottom: 0;
        }

        .info-table td {
            border: 1pt solid #000;
            padding: 4pt 5pt;
            font-size: 9pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        table th,
        table td {
            border: 1pt solid #000;
            padding: 6pt 5pt;
            font-size: 9pt;
            word-wrap: break-word;
        }

        table th {
            font-weight: bold;
            text-align: center;
        }

        .document-info {
            margin-top: 10pt;
            text-align: right;
            font-size: 8pt;
        }

        .signature-container {
            position: relative;
            display: inline-block;
            width: 90px;
            height: 20px;
            overflow: hidden;
        }

        .signature-image {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
            max-width: 90px;
            max-height: 20px;
            display: block;
            opacity: 0.7;
        }

        .signature-watermark {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 2px,
                rgba(0, 0, 0, 0.05) 2px,
                rgba(0, 0, 0, 0.05) 4px
            );
        }

        .signature-text-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            z-index: 3;
            font-size: 7pt;
            color: rgba(0, 0, 0, 0.1);
            font-weight: normal;
            white-space: nowrap;
            pointer-events: none;
            text-transform: uppercase;
            letter-spacing: 1.5pt;
        }

        .signature-border {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0.5pt solid rgba(0, 0, 0, 0.2);
            z-index: 4;
            pointer-events: none;
        }
    </style>
</head>
<body>
@php
    $number = 1;
@endphp

@if($attendees->count() > 0)
    <div class="page">
        <div class="content-wrapper">
            {{-- Header (only once at the top) --}}
            <table class="header-table" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td rowspan="2" width="85" style="border: 1pt solid #000; height: 70pt; vertical-align: middle; text-align: center; padding: 3pt; width: 85pt;">
                        <img src="{{ public_path('images/logoprc.png') }}" style="width: 65pt; height: 65pt;" alt="PRC Logo">
                    </td>
                    <td style="border: 1pt solid #000; text-align: center; font-weight: bold; padding: 2pt 0; height: 12pt; background-color: #FFFFFF; vertical-align: middle;">
                        Professional Regulation Commission
                    </td>
                </tr>
                <tr>
                    <td style="border: 1pt solid #000; text-align: center; font-weight: bold; padding: 6pt 0; height: 30pt; background-color: #dbe5f1; vertical-align: middle;">
                        <strong style="font-size: 14pt;">ATTENDANCE SHEET</strong>
                    </td>
                </tr>
            </table>

            <div class="main-title" style="margin-left: 85pt;">
                CPD COUNCIL OF/FOR PROFESSIONAL TEACHERS
            </div>

            {{-- Program / Date / Venue / Topics / Time / Room --}}
            <table class="info-table" style="margin-bottom: 0;">
                <tr>
                    <td colspan="3">
                        Title of the Program : {{ $seminar->title }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Date : {{ $seminar->date->format('F d, Y') }}
                    </td>
                    <td colspan="2">
                        Venue : {{ $seminar->venue ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Topic/s : {{ $seminar->topic ?? 'N/A' }}
                    </td>
                    <td>
                        Time : 
                        @if($seminar->time)
                            @php
                                $timeParts = explode(':', $seminar->time);
                                $hour = (int)$timeParts[0];
                                $minute = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
                                $ampm = $hour >= 12 ? 'PM' : 'AM';
                                $hour12 = $hour > 12 ? $hour - 12 : ($hour == 0 ? 12 : $hour);
                                echo sprintf('%d:%02d %s', $hour12, $minute, $ampm);
                            @endphp
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        Room : {{ $seminar->room ?? 'N/A' }}
                    </td>
                </tr>
            </table>

            {{-- Attendee Table --}}
            <table style="width: 100%; border-collapse: collapse; margin-top: 0;">
                <thead>
                    <tr>
                        <th width="5%" style="border: 1pt solid #000; padding: 6pt 5pt; font-size: 9pt; font-weight: bold; text-align: center;">NO.</th>
                        <th width="35%" style="border: 1pt solid #000; padding: 6pt 5pt; font-size: 9pt; font-weight: bold;">
                            NAME<br>
                            <span style="font-size: 8pt; font-weight: bold;">(First Name, Middle Name, Last Name)</span>
                        </th>
                        <th width="20%" style="border: 1pt solid #000; padding: 6pt 5pt; font-size: 9pt; font-weight: bold; text-align: center;">SIGNATURE</th>
                        <th width="20%" style="border: 1pt solid #000; padding: 6pt 5pt; font-size: 9pt; font-weight: bold;">PRC LICENSE NO.</th>
                        <th width="20%" style="border: 1pt solid #000; padding: 6pt 5pt; font-size: 9pt; font-weight: bold;">
                            EXPIRY DATE<br>
                            <span style="font-size: 8pt; font-weight: bold;">(DD/MM/YYYY)</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendees as $attendee)
                        <tr>
                            <td width="5%" style="border: 1pt solid #000; padding: 6pt 5pt; font-size: 9pt; text-align: center;">{{ $number++ }}</td>
                            <td width="35%" style="border: 1pt solid #000; padding: 6pt 5pt; font-size: 9pt; word-wrap: break-word;">{{ $attendee->full_name ?: $attendee->name }}</td>
                            <td width="20%" style="border: 1pt solid #000; padding: 6pt 5pt; font-size: 9pt; text-align: center;">
                                @if($attendee->hasSignature() && $attendee->signature_image)
                                    <div class="signature-container">
                                        <img
                                            src="data:image/png;base64,{{ $attendee->signature_image }}"
                                            alt="Signature"
                                            class="signature-image"
                                        >
                                        <div class="signature-watermark"></div>
                                        <div class="signature-text-overlay">VERIFIED</div>
                                        <div class="signature-border"></div>
                                    </div>
                                @endif
                            </td>
                            <td width="20%" style="border: 1pt solid #000; padding: 6pt 5pt; font-size: 9pt;">
                                @if ($attendee->isTeaching())
                                    {{ $attendee->prc_license_no ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td width="20%" style="border: 1pt solid #000; padding: 6pt 5pt; font-size: 9pt;">
                                @if ($attendee->isTeaching() && $attendee->prc_license_expiry)
                                    {{ $attendee->prc_license_expiry->format('d/m/Y') }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="document-info">
                CPDD-12-B<br>
                Rev. 00<br>
                June 29, 2020<br>
                <script type="text/php">
                    if (isset($pdf)) {
                        $text = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
                        $font = $fontMetrics->getFont("Arial");
                        $size = 8;
                        $y = $pdf->get_height() - 15;
                        $x = $pdf->get_width() - 100;
                        $pdf->page_text($x, $y, $text, $font, $size);
                    }
                </script>
            </div>
        </div>
    </div>
@else
    <div class="page">
        <p>No checked-in attendees found for this seminar.</p>
    </div>
@endif
</body>
</html>
