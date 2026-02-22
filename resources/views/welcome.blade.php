<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDO Balanga City - Attendance Monitoring System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #18309b;
            --dark-blue: #2d1360;
            --accent-cyan: #00bcd4;
            --text-blue: #1b297a;
            --bg-overlay-opacity: 0.7; /* Adjust this value: 0 = fully transparent, 1 = fully opaque */
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        /* Main Container */
        .page-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Bar - Purple - REDUCED SIZE */
        .header-bar {
            background: var(--primary-blue);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 100;
        }

        .logo-circle {
            /* Reduced logo size */
            --logo-size: 65px;
            
            width: var(--logo-size);
            height: var(--logo-size);
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
            overflow: hidden;
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            border-radius: 50%;
        }

        .header-title {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
            font-weight: 700;
            color: white;
            letter-spacing: 0.5px;
        }

        /* Content Section with Background - REDUCED PADDING */
        .content-section {
            flex: 1;
            background-image: url('{{ asset("images/SdoLandingBg.png") }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 2rem 1.5rem;
            position: relative;
        }

        /* Overlay for better readability */
        .content-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, var(--bg-overlay-opacity));
            z-index: 1;
        }

        .content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        /* Page Title Section - REDUCED SIZE */
        .title-panel {
            background: white;
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(60, 27, 122, 0.1);
        }

        .page-title {
            font-size: clamp(1.5rem, 3.5vw, 2.25rem);
            font-weight: 700;
            color: var(--text-blue);
            margin-bottom: 0.4rem;
            text-decoration: underline;
            text-decoration-color: var(--text-blue);
            text-decoration-thickness: 2px;
            text-underline-offset: 6px;
        }

        .page-subtitle {
            font-size: clamp(0.9rem, 1.8vw, 1.1rem);
            color: var(--text-blue);
            font-weight: 500;
        }

        /* Main Content Grid - REDUCED GAP */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        @media (min-width: 1024px) {
            .content-grid {
                grid-template-columns: 1.3fr 1fr;
                gap: 2rem;
            }
        }

        /* Welcome Card - REDUCED SIZE */
        .welcome-card {
            background: white;
            border: 3px solid var(--primary-blue);
            border-radius: 12px;
            padding: 1.5rem 2rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            height: fit-content;
            align-self: start;
        }

        .welcome-card p {
            color: var(--text-blue);
            font-size: 1rem;
            line-height: 1.7;
            font-weight: 500;
            margin: 0;
        }

        /* Action Cards Container - REDUCED GAP */
        .action-cards {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Action Card - REDUCED SIZE */
        .action-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem 2rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            border: 1px solid rgba(60, 27, 122, 0.1);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .card-icon svg {
            width: 40px;
            height: 40px;
            fill: var(--primary-blue);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-blue);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-description {
            color: #333;
            font-size: 0.9rem;
            margin-bottom: 1.25rem;
            line-height: 1.6;
        }

        /* Buttons - REDUCED SIZE */
        .btn {
            display: inline-block;
            padding: 0.7rem 1.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            text-align: center;
        }

        .btn-primary {
            background: var(--dark-blue);
            color: white;
            box-shadow: 0 4px 15px rgba(60, 27, 122, 0.3);
        }

        .btn-primary:hover {
            background: #4a2396;
            box-shadow: 0 6px 20px rgba(60, 27, 122, 0.4);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--accent-cyan);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 188, 212, 0.3);
        }

        .btn-secondary:hover {
            background: #00acc1;
            box-shadow: 0 6px 20px rgba(0, 188, 212, 0.4);
            transform: translateY(-2px);
        }

        /* Footer Bar - Purple - REDUCED SIZE */
        .footer-bar {
            background: var(--primary-blue);
            padding: 0;
            position: relative;
            z-index: 100;
        }

        .partner-logos {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            padding: 1.5rem 2rem;
            background: white;
            margin: 0;
            width: 100%;
        }

        .partner-logo {
            height: 55px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .partner-logo:hover {
            transform: scale(1.1);
        }

        .copyright {
            text-align: center;
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-bar {
                padding: 1rem 1.25rem;
                flex-direction: column;
                text-align: center;
            }

            .logo-circle {
                --logo-size: 60px;
            }

            .content-section {
                padding: 1.5rem 1rem;
            }

            .title-panel {
                padding: 1.25rem 1.5rem;
            }

            .welcome-card,
            .action-card {
                padding: 1.25rem 1.5rem;
            }

            .partner-logos {
                gap: 1.25rem;
            }

            .partner-logo {
                height: 45px;
            }
        }

        /* Shield Icon for Admin */
        .shield-icon {
            width: 40px;
            height: 40px;
        }

        /* Calendar Icon */
        .calendar-icon {
            width: 40px;
            height: 40px;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Purple Header Bar -->
        <header class="header-bar">
            <div class="logo-circle">
                <img src="{{ asset('images/sdodesignlogo.png') }}" alt="SDO Balanga City Logo">
            </div>
            <h1 class="header-title">SDO Balanga City</h1>
        </header>

        <!-- Main Content Section with Background -->
        <section class="content-section">
            <div class="content-wrapper">
                <!-- Page Title Panel -->
                <div class="title-panel">
                    <h2 class="page-title">Attendance Monitoring System</h2>
                    <p class="page-subtitle">Digitalizing Participation for more efficient DepED Balanga City</p>
                </div>

                <!-- Content Grid -->
                <div class="content-grid">
                    <!-- Left Column - Welcome Card -->
                    <div class="welcome-card">
                        <p>
                            Welcome to the SDO - Balanga City Attendance Monitoring System. This platform centralizes participation tracking for all seminars and meetings, ensuring accurate data, increased accountability, and faster processing of participation credits for all personnel.
                        </p>
                    </div>

                    <!-- Right Column - Action Cards -->
                    <div class="action-cards">
                        <!-- Administrator Login Card -->
                        <div class="action-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <svg class="shield-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2L4 6v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V6l-8-4z"/>
                                    </svg>
                                </div>
                                <h3 class="card-title">Administrator Login</h3>
                            </div>
                            <p class="card-description">
                                Manage Meetings & Seminars, Generate Reports and Monitor Participants logs.
                            </p>
                            <a href="{{ route('filament.admin.auth.login') }}" class="btn btn-primary">Sign in</a>
                        </div>

                        <!-- Attendee Login Card -->
                        <div class="action-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <svg class="calendar-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                </div>
                                <h3 class="card-title">Attendee Login</h3>
                            </div>
                            <p class="card-description">
                                Access your dashboard, view your universal QR code, and manage your profile.
                            </p>
                            <a href="{{ route('filament.attendee.auth.login') }}" class="btn btn-secondary">Sign in</a>
                        </div>

                        <!-- Upcoming Seminars Card -->
                        <div class="action-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <svg class="calendar-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
                                    </svg>
                                </div>
                                <h3 class="card-title">Upcoming Seminars</h3>
                            </div>
                            <p class="card-description">
                                View the schedule of active meetings and training sessions
                            </p>
                            <a href="#" class="btn btn-secondary">View Calendar</a>
                            {{-- <a href="{{ route('calendar') }}" class="btn btn-secondary">View Calendar</a> --}}
                            {{-- Uncomment above when calendar route is ready --}}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer-bar">
            <div class="partner-logos">
                <img src="{{ asset('images/depedlogo.png') }}" alt="DepED" class="partner-logo">
                <img src="{{ asset('images/BagongPilipinasLogo.png') }}" alt="Bagong Pilipinas" class="partner-logo">
                <img src="{{ asset('images/sdodesignlogo.png') }}" alt="SDO Balanga City" class="partner-logo">
                <img src="{{ asset('images/pgs-logo.png') }}" alt="PGS" class="partner-logo">
                <img src="{{ asset('images/Philippinequal.png') }}" alt="Philippine Quality Award" class="partner-logo">
            </div>
            <div class="copyright">
                All Rights Reserved | SDO BALANGA CITY {{ date('Y') }} - v1.0 | PIMAT
            </div>
        </footer>
    </div>
</body>
</html>