<?php

namespace App\Http\Controllers\Hotspot;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Models\Voucher;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    protected MikroTikService $mikrotik;

    public function __construct(MikroTikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Show hotspot login page
     */
    public function showLogin(Request $request)
    {
        return Inertia::render('Hotspot/Login', [
            'link' => $request->query('link'),
            'mac' => $request->query('mac'),
        ]);
    }

    /**
     * Process voucher login
     */
    public function login(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $voucher = Voucher::where('code', strtoupper($request->code))
            ->where('used', false)
            ->first();

        if (!$voucher) {
            return back()->withErrors([
                'code' => 'Invalid or already used voucher code.',
            ]);
        }

        // Check if voucher has expired
        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return back()->withErrors([
                'code' => 'This voucher has expired.',
            ]);
        }

        // Mark voucher as used
        $voucher->update(['used' => true]);

        // Attempt to login to MikroTik
        $success = $this->mikrotik->loginUser(
            $voucher->code,
            $voucher->code,
            $request->input('mac', '')
        );

        if (!$success) {
            \Log::error('MikroTik login failed for voucher: ' . $voucher->code);

            // Rollback voucher status
            $voucher->update(['used' => false]);

            return back()->withErrors([
                'code' => 'Login failed. Please try again or contact support.',
            ]);
        }

        // Create session record
        UserSession::create([
            'voucher_code' => $voucher->code,
            'mac_address' => $request->input('mac', 'unknown'),
            'ip_address' => $request->ip(),
            'started_at' => now(),
        ]);

        // Redirect to original URL or success page
        $redirectUrl = $request->input('link', '/hotspot/success');

        return redirect($redirectUrl)->with('success', 'Connected successfully!');
    }

    /**
     * Show success page
     */
    public function success()
    {
        return Inertia::render('Hotspot/Success');
    }
}
