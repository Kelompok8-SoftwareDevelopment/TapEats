<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTableNumber
{
    public function handle(Request $request, Closure $next)
    {
        // --- [DEV MODE ONLY] ---
        // Jika tidak ada session table_number, isi dengan default sementara
        if (!$request->session()->has('table_number')) {
            session(['table_number' => 99]); // Ganti dengan angka default meja test
        }

        return $next($request);
    }
}
