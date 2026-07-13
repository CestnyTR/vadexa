<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reports,
    ) {}

    public function index(): View
    {
        return view('reports.index', [
            'aging' => $this->reports->aging(),
            'collection' => $this->reports->collection(),
        ]);
    }
}
