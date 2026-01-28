<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tariff;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VoucherController extends Controller
{
    /**
     * Display a listing of vouchers
     */
    public function index(Request $request)
    {
        $query = Voucher::with('tariff', 'payment');

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'used') {
                $query->where('used', true);
            } elseif ($request->status === 'unused') {
                $query->where('used', false);
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }

        // Search by code
        if ($request->has('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        $vouchers = $query->latest()->paginate(20);

        return Inertia::render('Admin/Vouchers/Index', [
            'vouchers' => $vouchers,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Generate bulk vouchers
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'tariff_id' => 'required|exists:tariffs,id',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $tariff = Tariff::findOrFail($validated['tariff_id']);
        $vouchers = [];

        for ($i = 0; $i < $validated['quantity']; $i++) {
            $vouchers[] = Voucher::create([
                'code' => Str::upper(Str::random(10)),
                'tariff_id' => $tariff->id,
                'expires_at' => $tariff->duration_minutes
                    ? now()->addMinutes($tariff->duration_minutes)
                    : now()->addDays(30),
            ]);
        }

        return redirect()->route('vouchers.index')
            ->with('success', count($vouchers) . ' vouchers generated successfully.');
    }

    /**
     * Show voucher details
     */
    public function show(Voucher $voucher)
    {
        $voucher->load('tariff', 'payment');

        return Inertia::render('Admin/Vouchers/Show', [
            'voucher' => $voucher,
        ]);
    }
}
