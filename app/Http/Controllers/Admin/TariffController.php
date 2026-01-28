<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tariff;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TariffController extends Controller
{
    protected MikroTikService $mikrotik;

    public function __construct(MikroTikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    /**
     * Display a listing of tariffs
     */
    public function index()
    {
        $tariffs = Tariff::with('vouchers', 'payments')->latest()->get();

        return Inertia::render('Admin/Tariffs/Index', [
            'tariffs' => $tariffs,
        ]);
    }

    /**
     * Show the form for creating a new tariff
     */
    public function create()
    {
        return Inertia::render('Admin/Tariffs/Create');
    }

    /**
     * Store a newly created tariff
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'data_limit_bytes' => 'nullable|integer|min:1',
            'download_speed_kbps' => 'required|integer|min:1',
            'upload_speed_kbps' => 'required|integer|min:1',
        ]);

        $tariff = Tariff::create($validated);

        // Create MikroTik profile
        $this->mikrotik->createProfile($tariff);

        return redirect()->route('tariffs.index')
            ->with('success', 'Tariff created successfully.');
    }

    /**
     * Show the form for editing a tariff
     */
    public function edit(Tariff $tariff)
    {
        return Inertia::render('Admin/Tariffs/Edit', [
            'tariff' => $tariff,
        ]);
    }

    /**
     * Update the specified tariff
     */
    public function update(Request $request, Tariff $tariff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'data_limit_bytes' => 'nullable|integer|min:1',
            'download_speed_kbps' => 'required|integer|min:1',
            'upload_speed_kbps' => 'required|integer|min:1',
        ]);

        $tariff->update($validated);

        // Update MikroTik profile
        $this->mikrotik->createProfile($tariff);

        return redirect()->route('tariffs.index')
            ->with('success', 'Tariff updated successfully.');
    }

    /**
     * Remove the specified tariff
     */
    public function destroy(Tariff $tariff)
    {
        $tariff->delete();

        return redirect()->route('tariffs.index')
            ->with('success', 'Tariff deleted successfully.');
    }
}
