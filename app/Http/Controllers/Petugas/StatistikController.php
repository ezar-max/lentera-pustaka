<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Services\StatistikService;
use Illuminate\View\View;

class StatistikController extends Controller
{
    public function __construct(public StatistikService $statistikService) {}

    public function index(): View
    {
        $statistik = $this->statistikService->ambilSemuaStatistik();

        return view('petugas.statistik', compact('statistik'));
    }
}
