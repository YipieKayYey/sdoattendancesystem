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
            margin-top: 4pt;
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6pt;
            margin-bottom: 0;
            border-spacing: 0;
        }

        .info-table td {
            border: 1pt solid #000;
            padding: 2pt 5pt;
            font-size: 9pt;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        table th,
        table td {
            border: 1pt solid #000;
            padding: 10pt 8pt;
            font-size: 11pt;
            min-height: 28pt;
            word-wrap: break-word;
        }

        table th {
            font-weight: bold;
            text-align: center;
        }

        /* Show table header only on page 1, do not repeat on subsequent pages */
        thead {
            display: table-row-group;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10pt;
            font-size: 11pt;
            border: 1pt solid #000;
        }

        .footer-table tr {
            border: none;
        }

        .footer-table td {
            border-right: 1pt solid #000;
            border-top: none;
            border-bottom: 1pt solid #000;
            padding: 10pt 12pt;
            vertical-align: top;
            width: 50%;
            min-height: 100pt;
        }

        .footer-table td:first-child {
            border-left: none;
        }

        .footer-table td:last-child {
            border-right: none;
        }

        .footer-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 2pt;
        }

        .footer-small {
            font-size: 9pt;
            text-align: center;
            margin-top: 2pt;
        }

        .footer-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-inner td {
            border: none !important;
            padding: 0;
            vertical-align: top;
        }

        .footer-spacer {
            height: 0pt;
        }

        .signature-line {
            display: block;
            text-align: center;
            margin: 2pt 0 2pt 0;
            font-size: 11pt;
            letter-spacing: 1pt;
        }

        .footer-small-line {
            font-size: 9pt;
            text-align: center;
            margin-top: 1pt;
        }

        .footer-datetime {
            margin-top: 0;
            padding-top: 8pt;
            font-size: 11pt;
        }

        .footer-datetime-spacer {
            height: 16pt;
            min-height: 16pt;
        }

        .document-info {
            margin-top: 10pt;
            text-align: right;
            font-size: 9pt;
        }

        .signature-container {
            position: relative;
            display: inline-block;
            width: 110px;
            height: 28px;
            overflow: hidden;
        }

        .signature-image {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
            max-width: 110px;
            max-height: 28px;
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
                <colgroup>
                    <col style="width: 33%;">
                    <col style="width: 33%;">
                    <col style="width: 34%;">
                </colgroup>
                <tr>
                    <td colspan="3" style="padding: 5pt 5pt;">
                        Title of the Program : {{ $seminar->title }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="padding: 0; vertical-align: top; border: none;">
                        <table style="width: 100%; border-collapse: collapse; margin: 0;">
                            <tr>
                                <td style="width: 28%; border: 1pt solid #000; padding: 8pt 5pt; font-size: 9pt;">Date : {{ $seminar->date->format('F d, Y') }}</td>
                                <td style="width: 72%; border: 1pt solid #000; padding: 8pt 5pt; font-size: 9pt;">Venue : {{ $seminar->venue ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>Topic/s : {{ $seminar->topic ?? 'N/A' }}</td>
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
                    <td style="background-color: #a6a6a6;">Room : {{ $seminar->room ?? 'N/A' }}</td>
                </tr>
            </table>

            {{-- Attendee Table --}}
            <table style="width: 100%; border-collapse: collapse; margin-top: 0;">
                <thead>
                    <tr>
                        <th width="5%" style="border: 1pt solid #000; padding: 10pt 8pt; font-size: 11pt; font-weight: bold; text-align: center;">NO.</th>
                        <th width="35%" style="border: 1pt solid #000; padding: 10pt 8pt; font-size: 11pt; font-weight: bold;">
                            NAME<br>
                            <span style="font-size: 9pt; font-weight: bold;">(First Name, Middle Name, Last Name)</span>
                        </th>
                        <th width="20%" style="border: 1pt solid #000; padding: 10pt 8pt; font-size: 11pt; font-weight: bold; text-align: center;">SIGNATURE</th>
                        <th width="20%" style="border: 1pt solid #000; padding: 10pt 8pt; font-size: 11pt; font-weight: bold;">PRC LICENSE NO.</th>
                        <th width="20%" style="border: 1pt solid #000; padding: 10pt 8pt; font-size: 11pt; font-weight: bold;">
                            EXPIRY DATE<br>
                            <span style="font-size: 9pt; font-weight: bold;">(DD/MM/YYYY)</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendees as $attendee)
                        <tr>
                            <td width="5%" style="border: 1pt solid #000; padding: 10pt 8pt; font-size: 11pt; text-align: center;">{{ $number++ }}</td>
                            <td width="35%" style="border: 1pt solid #000; padding: 10pt 8pt; font-size: 11pt; word-wrap: break-word;">{{ $attendee->full_name ?? $attendee->name }}</td>
                            <td width="20%" style="border: 1pt solid #000; padding: 10pt 8pt; font-size: 11pt; text-align: center;">
                                @if (!($blankSignatures ?? false) && $attendee->hasSignature() && $attendee->signature_image)
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
                            <td width="20%" style="border: 1pt solid #000; padding: 10pt 8pt; font-size: 11pt;">
                                @if ($attendee->isTeaching())
                                    {{ $attendee->prc_license_no ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td width="20%" style="border: 1pt solid #000; padding: 10pt 8pt; font-size: 11pt;">
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

            {{-- Footer Signature Boxes --}}
            <table class="footer-table">
                <tr>
                    <td style="width: 50%; vertical-align: bottom; padding-bottom: 8pt;">
                        <table class="footer-inner">
                            <tr>
                                <td>
                                    <div class="footer-title">Concurred by:</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="footer-spacer"></td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="signature-line">______________________________</span>
                                    <div class="footer-small">(Signature Over Printed Name)</div>
                                    <div class="footer-small-line">CPD Provider's Authorized Representative</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="footer-datetime-spacer" style="border: none; padding: 0; height: 16pt;"></td>
                            </tr>
                            <tr>
                                <td class="footer-datetime" style="border: none; padding: 0;">
                                    Date and Time:
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; vertical-align: bottom; padding-bottom: 8pt;">
                        <table class="footer-inner">
                            <tr>
                                <td>
                                    <div class="footer-title">Certified Correct by:</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="footer-spacer"></td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="signature-line">______________________________</span>
                                    <div class="footer-small">(Signature Over Printed Name)</div>
                                    <div class="footer-small-line">CPD Provider's Authorized Representative</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="footer-datetime-spacer" style="border: none; padding: 0; height: 16pt;"></td>
                            </tr>
                            <tr>
                                <td class="footer-datetime" style="border: none; padding: 0;">
                                    Date and Time:
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="document-info">
                CPDD-12-B<br>
                Rev. 00<br>
                June 29, 2020<br>
                Page PAGE___of___<br>
                NUMPAGES___
            </div>
        </div>
    </div>
@else
    <div class="page">
        <div class="content-wrapper">
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

            <p style="margin-top: 20pt;">No checked-in attendees found for this seminar.</p>

            {{-- Footer Signature Boxes --}}
            <table class="footer-table" style="margin-top: 20pt;">
                <tr>
                    <td style="width: 50%; vertical-align: bottom; padding-bottom: 8pt;">
                        <table class="footer-inner">
                            <tr>
                                <td>
                                    <div class="footer-title">Concurred by:</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="footer-spacer"></td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="signature-line">______________________________</span>
                                    <div class="footer-small">(Signature Over Printed Name)</div>
                                    <div class="footer-small-line">CPD Provider's Authorized Representative</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="footer-datetime-spacer" style="border: none; padding: 0; height: 16pt;"></td>
                            </tr>
                            <tr>
                                <td class="footer-datetime" style="border: none; padding: 0;">
                                    Date and Time:
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; vertical-align: bottom; padding-bottom: 8pt;">
                        <table class="footer-inner">
                            <tr>
                                <td>
                                    <div class="footer-title">Certified Correct by:</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="footer-spacer"></td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="signature-line">______________________________</span>
                                    <div class="footer-small">(Signature Over Printed Name)</div>
                                    <div class="footer-small-line">CPD Provider's Authorized Representative</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="footer-datetime-spacer" style="border: none; padding: 0; height: 16pt;"></td>
                            </tr>
                            <tr>
                                <td class="footer-datetime" style="border: none; padding: 0;">
                                    Date and Time:
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="document-info">
                CPDD-12-B<br>
                Rev. 00<br>
                June 29, 2020<br>
                Page PAGE___of___<br>
                NUMPAGES___
            </div>
        </div>
    </div>
@endif
</body>
</html>
