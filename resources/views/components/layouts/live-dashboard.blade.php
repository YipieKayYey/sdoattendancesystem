<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Dashboard - {{ $seminarTitle ?? 'Seminar' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        'primary': '#18309b',
                        'primary-blue': '#18309b',
                        'dark-blue': '#2d1360',
                        'accent': '#00bcd4',
                        'accent-cyan': '#00bcd4',
                        'text-blue': '#1b297a',
                    }
                }
            }
        }
    </script>
    @livewireStyles
</head>
<body class="min-h-screen antialiased font-sans bg-slate-100" style="font-family: 'Poppins', sans-serif;">
    {{ $slot }}
    @livewireScripts
</body>
</html>
