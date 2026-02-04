<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seminar Ticket</title>
    <style>
        @page {
            margin: 0;
            size: letter;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            padding: 60px 40px;
            background: #ffffff;
            color: #1a1a1a;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f59e0b;
        }
        .header h1 {
            font-size: 32px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .header p {
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .ticket-info {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .ticket-info h2 {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 25px;
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }
        .ticket-details {
            display: grid;
            gap: 12px;
        }
        .ticket-details .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .ticket-details .detail-row:last-child {
            border-bottom: none;
        }
        .ticket-details .label {
            font-weight: 600;
            color: #4b5563;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .ticket-details .value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
            text-align: right;
        }
        .ticket-number {
            background: #1f2937;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            letter-spacing: 1px;
        }
        .qr-section {
            text-align: center;
            margin: 40px 0;
            padding: 30px;
            background: #ffffff;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
        }
        .qr-code {
            display: inline-block;
            padding: 20px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .qr-section h3 {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        .footer p {
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Seminar Ticket</h1>
            <p>Entry Pass</p>
        </div>
        
        <div class="ticket-info">
            <h2>{{ $attendee->seminar->title }}</h2>
            <div class="ticket-details">
                <div class="detail-row">
                    <span class="label">Name:</span>
                    <span class="value">{{ $attendee->full_name ?? $attendee->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Email:</span>
                    <span class="value">{{ $attendee->email }}</span>
                </div>
                @if($attendee->mobile_phone)
                <div class="detail-row">
                    <span class="label">Mobile Phone:</span>
                    <span class="value">{{ $attendee->mobile_phone }}</span>
                </div>
                @endif
                @if($attendee->position)
                <div class="detail-row">
                    <span class="label">Position:</span>
                    <span class="value">{{ $attendee->position }}</span>
                </div>
                @endif
                @if($attendee->isTeaching() && $attendee->prc_license_no)
                <div class="detail-row">
                    <span class="label">PRC License No:</span>
                    <span class="value">{{ $attendee->prc_license_no }}</span>
                </div>
                @endif
                @if($attendee->isTeaching() && $attendee->prc_license_expiry)
                <div class="detail-row">
                    <span class="label">PRC Expiry:</span>
                    <span class="value">{{ $attendee->prc_license_expiry->format('d/m/Y') }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="label">Date:</span>
                    <span class="value">
                        {{ $attendee->seminar->date->format('F j, Y') }}
                        @if(!$attendee->seminar->isMultiDay() && $attendee->seminar->time)
                            @ {{ $attendee->seminar->formatted_time }}
                        @endif
                    </span>
                </div>
                @if($attendee->seminar->venue)
                <div class="detail-row">
                    <span class="label">Venue:</span>
                    <span class="value">{{ $attendee->seminar->venue }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="label">Ticket Number:</span>
                    <span class="ticket-number">{{ $attendee->ticket_hash }}</span>
                </div>
            </div>
        </div>
        
        <div class="qr-section">
            <h3>QR Code for Check-in</h3>
            <div class="qr-code">
                {!! $barcodeImage !!}
            </div>
        </div>
        
        <div class="footer">
            <p>Please present this ticket at the venue. </p>
        </div>
    </div>
</body>
</html>
