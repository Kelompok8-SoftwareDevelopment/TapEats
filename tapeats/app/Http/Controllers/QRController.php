<?php

namespace App\Http\Controllers;

use App\Models\Barcodes;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QRController extends Controller
{
    public function storeResult(Request $request): JsonResponse
    {
        $request->validate([
            'table_number' => 'required|string',
        ]);

        session(['table_number' => $request->table_number]);

        return response()->json(['status' => 'success']);
    }

    public function checkCode($code)
    {
        if (preg_match('/^[a-zA-Z]\d{4}$/', $code)) {
            $exists = Barcodes::where('table_number', $code)->exists();

            if ($exists) {
                session(['table_number' => $code]);
                return redirect()->route('home')->with('message', 'Welcome! Code verified successfully.');
            } else {
                return view('invalid', [
                    'message' => 'Code not found in the database.',
                ]);
            }
        }

        return view('invalid', [
            'message' => 'Invalid code format.',
        ]);
    }
}
