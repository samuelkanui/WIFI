<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function payments(Request $request)
    {
        $query = Payment::with('tariff');

        // Date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(20);
        $totalRevenue = Payment::where('status', 'paid')->sum('amount');

        return Inertia::render('Admin/Reports/Payments', [
            'payments' => $payments,
            'totalRevenue' => $totalRevenue,
            'filters' => $request->only(['start_date', 'end_date', 'status']),
        ]);
    }

    public function sessions(Request $request)
    {
        $query = UserSession::query();

        // Search by voucher or MAC
        if ($request->has('search')) {
            $query->where('voucher_code', 'like', '%' . $request->search . '%')
                ->orWhere('mac_address', 'like', '%' . $request->search . '%');
        }

        $sessions = $query->latest()->paginate(20);

        return Inertia::render('Admin/Reports/Sessions', [
            'sessions' => $sessions,
            'filters' => $request->only(['search']),
        ]);
    }
}
