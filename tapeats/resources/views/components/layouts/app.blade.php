<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? "OLLIE'S" }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo_ollies2_favicon1.png') }}">

    {{-- Livewire & App Styles --}}
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />

    {{-- HTML5 QR Scanner --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.7/html5-qrcode.min.js"></script>
</head>

<body class="{{ $class ?? '' }} mx-auto max-w-md bg-[#f5f5f9]">
    {{ $slot }}

    @livewireScripts
    @stack('scripts')
</body>
</html>