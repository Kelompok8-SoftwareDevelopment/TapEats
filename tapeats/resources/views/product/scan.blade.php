<div class="flex flex-col items-center justify-center h-screen">
    <h2 class="text-xl font-bold mb-4">Scan QR Code</h2>
    <div id="reader" class="w-80 h-80"></div>
</div>

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="{{ asset('js/qr/scanner.js') }}"></script>
@endpush
