<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analytics Report - {{ $seminar->title }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4;
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 20px;
        }
        
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin: 10px 0 5px 0;
        }
        
        .subtitle {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin: 5px 0;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-rate {
            font-size: 10px;
            color: #059669;
            margin-top: 3px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th {
            background: #1e40af;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            font-size: 11px;
        }
        
        .table tr:nth-child(even) {
            background: #f8fafc;
        }
        
        .progress-bar {
            width: 100px;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            display: inline-block;
            margin-right: 8px;
        }
        
        .progress-fill {
            height: 100%;
            background: #1e40af;
            border-radius: 4px;
        }
        
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        
        .breakdown-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .breakdown-label {
            flex: 1;
            font-size: 11px;
        }
        
        .breakdown-value {
            font-weight: bold;
            font-size: 11px;
            margin-left: 10px;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="title">SEMINAR ANALYTICS REPORT</div>
        <div class="subtitle">{{ $seminar->title }}</div>
        <div class="subtitle">{{ $seminar->date->format('F j, Y') }}</div>
        <div class="subtitle">Generated: {{ $generated_at }}</div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Registrations</div>
            <div class="stat-number">{{ $total_registrations }}</div>
            <div class="stat-rate">{{ $seminar->is_open ? 'Open Seminar' : ($seminar->capacity ? 'Capacity: ' . $seminar->capacity : 'Limited') }}</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">Checked In</div>
            <div class="stat-number">{{ $total_checked_in }}</div>
            <div class="stat-rate">{{ $check_in_rate }}% Rate</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">Checked Out</div>
            <div class="stat-number">{{ $total_checked_out }}</div>
            <div class="stat-rate">{{ $check_out_rate }}% Rate</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">Seminar Type</div>
            <div class="stat-number" style="font-size: 16px;">{{ $is_multi_day ? 'Multi-Day' : 'Single Day' }}</div>
            <div class="stat-rate">{{ $is_multi_day ? $seminar->days_count . ' Days' : '1 Day' }}</div>
        </div>
    </div>

    <!-- Breakdown Section -->
    <div class="two-column">
        <!-- Personnel Type Breakdown -->
        <div class="section">
            <div class="section-title">Personnel Type Breakdown</div>
            @foreach($personnel_breakdown as $type => $count)
                <div class="breakdown-item">
                    <div class="breakdown-label">{{ ucfirst($type) }}</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ ($count / $total_registrations) * 100 }}%"></div>
                    </div>
                    <div class="breakdown-value">{{ $count }} ({{ round(($count / $total_registrations) * 100, 1) }}%)</div>
                </div>
            @endforeach
        </div>

        <!-- Gender Breakdown -->
        <div class="section">
            <div class="section-title">Gender Distribution</div>
            @foreach($gender_breakdown as $gender => $count)
                <div class="breakdown-item">
                    <div class="breakdown-label">{{ ucfirst($gender) }}</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ ($count / $total_registrations) * 100 }}%; background: #059669;"></div>
                    </div>
                    <div class="breakdown-value">{{ $count }} ({{ round(($count / $total_registrations) * 100, 1) }}%)</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Top Schools -->
    <div class="section">
        <div class="section-title">Top Participating Schools/Offices</div>
        <table class="table">
            <thead>
                <tr>
                    <th>School/Office/Agency</th>
                    <th class="text-center">Count</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_schools as $school => $count)
                    <tr>
                        <td>{{ $school }}</td>
                        <td class="text-center">{{ $count }}</td>
                        <td class="text-right">{{ round(($count / $total_registrations) * 100, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Daily Attendance (Multi-Day) -->
    @if($is_multi_day)
        <div class="section">
            <div class="section-title">Daily Attendance Breakdown</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>Venue</th>
                        <th class="text-center">Checked In</th>
                        <th class="text-center">Checked Out</th>
                        <th class="text-right">Check-in Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($daily_attendance as $day)
                        <tr>
                            <td>Day {{ $day['day'] }}</td>
                            <td>{{ $day['date'] }}</td>
                            <td>{{ $day['start_time'] ?? 'N/A' }}</td>
                            <td>{{ $day['venue'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $day['checked_in'] }}</td>
                            <td class="text-center">{{ $day['checked_out'] }}</td>
                            <td class="text-right">{{ $total_registrations > 0 ? round(($day['checked_in'] / $total_registrations) * 100, 1) . '%' : '0%' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>© {{ date('Y') }} DepEd SDO Balanga City - Attedance Monitoring System</p>
        <p>Report generated on {{ $generated_at }} • Total Records: {{ $total_registrations }}</p>
    </div>
</body>
</html>
