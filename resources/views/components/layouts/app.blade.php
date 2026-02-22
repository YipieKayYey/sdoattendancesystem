<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Seminar Registration' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-50">
    {{ $slot }}
    @livewireScripts
    <script>
        window.openPrivacyPolicy = function() {
            var m = document.getElementById('privacyModal');
            if (m) m.classList.remove('hidden');
        };
        window.closePrivacyPolicy = function() {
            var m = document.getElementById('privacyModal');
            if (m) m.classList.add('hidden');
        };
    </script>
</body>
</html>
