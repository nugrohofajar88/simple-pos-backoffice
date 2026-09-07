<?php

namespace App\Http\Controllers;

use App\Support\ReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    public function index(): View
    {
        return view('dashboard', [
            'summary' => $this->reports->summary(),
        ]);
    }
}
