<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        try {
            Mail::to('ruangaitiku@gmail.com')->send(new NewsletterSubscription($validated['email'], $validated['message']));

            return response()->json([
                'message' => 'Pesan Anda berhasil terkirim! Terima kasih telah menghubungi kami.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim pesan. Silakan coba lagi nanti.'
            ], 500);
        }
    }
}
