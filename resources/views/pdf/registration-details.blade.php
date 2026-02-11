<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Registration Details - {{ $attendee->full_name ?? $attendee->name }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .logo {
            max-width: 60px;
            margin-bottom: 8px;
        }
        
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            margin: 8px 0;
        }
        
        .subtitle {
            font-size: 12px;
            color: #64748b;
            margin: 4px 0;
        }
        
        .section {
            margin-bottom: 18px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            border-left: 4px solid #2563eb;
            padding-left: 8px;
        }
        
        .detail-row {
            margin-bottom: 6px;
            display: flex;
        }
        
        .detail-label {
            font-weight: 600;
            min-width: 110px;
            color: #475569;
        }
        
        .detail-value {
            flex: 1;
            color: #1e293b;
        }
        
        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
        }
        
        .qr-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
        }
        
        .ticket-code {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            background: #f1f5f9;
            padding: 6px;
            border-radius: 4px;
            margin: 8px 0;
            word-break: break-all;
        }
        
        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 9px;
            color: #64748b;
        }
        
        .success-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .personnel-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .personnel-teaching {
            background: #dcfce7;
            color: #166534;
        }
        
        .personnel-non-teaching {
            background: #f3f4f6;
            color: #374151;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <img src="{{ public_path('images/sdodesignlogo.png') }}" alt="SDO Logo" class="logo" onerror="this.style.display='none'">
        <div class="title">Registration Details</div>
        <div class="subtitle">Schools Division Office Attendance Monitoring System</div>
        <div class="success-badge">Registration Successful</div>
    </div>

    <!-- Seminar Information -->
    <div class="section">
        <div class="section-title">Seminar Information</div>
        <div class="detail-row">
            <span class="detail-label">Seminar Title:</span>
            <span class="detail-value">{{ $seminar->title }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date & Time:</span>
            <span class="detail-value">
                @if($seminar->isMultiDay())
                    @foreach($seminar->days as $index => $day)
                        {{ $day->date->format('F j, Y') }}
                        @if($day->start_time)
                            @ {{ $day->formatted_time }}
                        @else
                            @ (Time not set)
                        @endif
                        @if($index < $seminar->days->count() - 1) • @endif
                    @endforeach
                @else
                    {{ $seminar->date->format('F j, Y') }}
                    @if($seminar->time)
                        @ {{ $seminar->formatted_time }}
                    @else
                        @ (Time not set)
                    @endif
                @endif
            </span>
        </div>
        @if(!$seminar->isMultiDay() && $seminar->venue)
        <div class="detail-row">
            <span class="detail-label">Venue:</span>
            <span class="detail-value">{{ $seminar->venue }}</span>
        </div>
        @endif
        @if($seminar->isMultiDay())
        <div class="detail-row">
            <span class="detail-label">Venues:</span>
            <span class="detail-value">
                @foreach($seminar->days as $index => $day)
                    @if($day->venue)
                        Day {{ $day->day_number }}: {{ $day->venue }}
                        @if($index < $seminar->days->count() - 1) • @endif
                    @endif
                @endforeach
            </span>
        </div>
        @endif
    </div>

    <!-- Attendee Information -->
    <div class="section">
        <div class="section-title">Attendee Information</div>
        <div class="detail-row">
            <span class="detail-label">Full Name:</span>
            <span class="detail-value">{{ $attendee->full_name ?? $attendee->name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value">{{ $attendee->email }}</span>
        </div>
        @if($attendee->mobile_phone)
        <div class="detail-row">
            <span class="detail-label">Mobile Phone:</span>
            <span class="detail-value">{{ $attendee->mobile_phone }}</span>
        </div>
        @endif
        @if($attendee->position)
        <div class="detail-row">
            <span class="detail-label">Position:</span>
            <span class="detail-value">{{ $attendee->position }}</span>
        </div>
        @endif
        @if($attendee->personnel_type)
        <div class="detail-row">
            <span class="detail-label">Personnel Type:</span>
            <span class="detail-value">
                <span class="personnel-badge {{ $attendee->personnel_type === 'teaching' ? 'personnel-teaching' : 'personnel-non-teaching' }}">
                    {{ $attendee->personnel_type === 'teaching' ? 'Teaching' : 'Non-Teaching' }}
                </span>
            </span>
        </div>
        @endif
        @if($attendee->isTeaching() && $attendee->prc_license_no)
        <div class="detail-row">
            <span class="detail-label">PRC License No:</span>
            <span class="detail-value">{{ $attendee->prc_license_no }}</span>
        </div>
        @endif
        @if($attendee->isTeaching() && $attendee->prc_license_expiry)
        <div class="detail-row">
            <span class="detail-label">PRC Expiry:</span>
            <span class="detail-value">{{ $attendee->prc_license_expiry->format('F j, Y') }}</span>
        </div>
        @endif
        @if($attendee->hasSignature())
        <div class="detail-row">
            <span class="detail-label">Signature:</span>
            <span class="detail-value">
                <span class="success-badge">Captured</span>
                @if($attendee->signature_timestamp)
                <br><small>Signed on {{ $attendee->signature_timestamp->format('F j, Y g:i A') }}</small>
                @endif
            </span>
        </div>
        @endif
    </div>

    <!-- QR Code Section -->
    <div class="qr-section">
        <div class="qr-title">Your Ticket QR Code</div>
        <div style="text-align: center; margin: 15px 0;">
            <div style="display: inline-block;">
                {!! DNS2D::getBarcodeHTML($attendee->ticket_hash, 'QRCODE', 8, 8) !!}
            </div>
        </div>
        <div class="ticket-code">{{ $attendee->ticket_hash }}</div>
        <div style="font-size: 10px; color: #64748b; margin-top: 10px;">
            Present this code at the event for check-in
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>Generated on {{ $generatedAt->format('F j, Y g:i A') }}</div>
        <div>Schools Division Office Attendance Monitoring System</div>
        <div style="margin-top: 5px;">This is an official registration confirmation document.</div>
    </div>
</body>
</html>
