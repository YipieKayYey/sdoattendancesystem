<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Universal QR Code - {{ $profile->full_name }}</title>
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
        }
        
        .detail-label {
            font-weight: 600;
            min-width: 110px;
            color: #475569;
            display: inline-block;
        }
        
        .detail-value {
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
        
        .qr-code {
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
        
        .badge {
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
        <div class="title">Universal QR Code</div>
        <div class="subtitle">Schools Division Office Attendance Monitoring System</div>
    </div>

    <!-- Profile Information -->
    <div class="section">
        <div class="section-title">Attendee Information</div>
        <div class="detail-row">
            <span class="detail-label">Full Name:</span>
            <span class="detail-value">{{ $profile->full_name }}</span>
        </div>
        @if($profile->user)
        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value">{{ $profile->user->email }}</span>
        </div>
        @endif
        @if($profile->mobile_phone)
        <div class="detail-row">
            <span class="detail-label">Mobile Phone:</span>
            <span class="detail-value">{{ $profile->mobile_phone }}</span>
        </div>
        @endif
        @if($profile->position)
        <div class="detail-row">
            <span class="detail-label">Position:</span>
            <span class="detail-value">{{ $profile->position }}</span>
        </div>
        @endif
        @if($profile->personnel_type)
        <div class="detail-row">
            <span class="detail-label">Personnel Type:</span>
            <span class="detail-value">
                <span class="badge {{ $profile->personnel_type === 'teaching' ? 'personnel-teaching' : 'personnel-non-teaching' }}">
                    {{ $profile->personnel_type === 'teaching' ? 'Teaching' : 'Non-Teaching' }}
                </span>
            </span>
        </div>
        @endif
        @if($profile->school_office_agency)
        <div class="detail-row">
            <span class="detail-label">School/Office:</span>
            <span class="detail-value">{{ $profile->school_office_agency }}</span>
        </div>
        @endif
        @if($profile->prc_license_no)
        <div class="detail-row">
            <span class="detail-label">PRC License No:</span>
            <span class="detail-value">{{ $profile->prc_license_no }}</span>
        </div>
        @endif
        @if($profile->prc_license_expiry)
        <div class="detail-row">
            <span class="detail-label">PRC Expiry:</span>
            <span class="detail-value">{{ $profile->prc_license_expiry->format('F j, Y') }}</span>
        </div>
        @endif
    </div>

    <!-- QR Code Section -->
    <div class="qr-section">
        <div class="qr-title">Your Universal QR Code</div>
        <div style="text-align: center; margin: 15px 0;">
            <div style="display: inline-block;">
                {!! DNS2D::getBarcodeHTML($profile->universal_qr_hash, 'QRCODE', 8, 8) !!}
            </div>
        </div>
        <div class="qr-code">{{ $profile->universal_qr_hash }}</div>
        <div style="font-size: 10px; color: #64748b; margin-top: 10px;">
            Show this QR code when checking in at any seminar
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>Generated on {{ $generatedAt->format('F j, Y g:i A') }}</div>
        <div>Schools Division Office Attendance Monitoring System</div>
        <div style="margin-top: 5px;">This is your universal QR code for seminar check-in.</div>
    </div>
</body>
</html>
