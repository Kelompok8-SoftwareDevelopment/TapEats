<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? "TapEats" }}</title>

    @vite(["resources/css/app.css", "resources/js/app.js"])

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}"></script>

    <script src="https://unpkg.com/html5-qrcode@2.3.7/html5-qrcode.min.js"></script>

    @livewireStyles
</head>
<body class="{{ $class ?? '' }} mx-auto max-w-md bg-[#f5f5f9]">
    {{ $slot }}

    @livewireScripts
    @stack('scripts')
</body>
</html>
