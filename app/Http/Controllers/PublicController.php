<?php

namespace App\Http\Controllers;

use App\Models\Tariff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicController extends Controller
{
    /**
     * Display available tariffs for purchase
     */
    public function tariffs()
    {
        $tariffs = Tariff::orderBy('price', 'asc')->get();

        return Inertia::render('Public/Tariffs', [
            'tariffs' => $tariffs,
        ]);
    }
}
