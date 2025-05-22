<!DOCTYPE html>
<html lang="en">
@livewireStyles

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(["resources/css/app.css", "resources/js/app.js"])
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- <script src="{{ asset('js/app.js') }}"></script> -->

    <script
        src="https://unpkg.com/html5-qrcode"
        type="text/javascript"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet" />



    <title>{{ $title ?? "TapEats" }}</title>
</head>
@livewireScripts

<body class="{{ $class ?? "" }} mx-auto max-w-md bg-[#f5f5f9]">
    {{ $slot }}
</body>

</html>