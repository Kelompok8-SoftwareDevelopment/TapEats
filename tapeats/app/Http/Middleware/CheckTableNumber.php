<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTableNumber
{
    public function handle(Request $request, Closure $next)
    {
        // Izinkan akses ke halaman scan dan store QR
        if ($request->is('scan') || $request->is('store-qr-result')) {
            return $next($request);
        }

        // Cek apakah table_number ada di session
        if (!$request->session()->has('table_number')) {
            return redirect()->route('product.scan');
        }

        return $next($request);
    }
}
