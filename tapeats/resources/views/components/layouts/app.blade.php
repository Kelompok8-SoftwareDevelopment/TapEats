<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <!-- @vite(["resources/css/app.css", "resources/js/app.js"]) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <!-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> -->
    <!-- <script src="{{ asset('js/app.js') }}"></script> -->

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}"></script>

    <script src="https://unpkg.com/html5-qrcode@2.3.7/html5-qrcode.min.js"></script>



    <title>{{ $title ?? "TapEats" }}</title>
    @vite('resources/css/app.css')
</head>

<body class="{{ $class ?? '' }} mx-auto max-w-md bg-[#f5f5f9]">
    {{ $slot }}

    @livewireScripts
    @vite('resources/js/app.js')
</body>

</html>