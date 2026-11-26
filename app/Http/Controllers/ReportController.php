<?php

namespace App\Http\Controllers;

use App\Enums\ReportPeriod;
use App\Exports\ReportExport;
use App\Http\Requests\ReportRequest;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(ReportRequest $request, ReportService $service): View
    {
        [$period, $date] = $this->parameters($request);

        return view('reports.index', [
            'report' => $service->build($request->user(), $period, $date),
            'period' => $period,
            'date' => $date,
        ]);
    }

    public function pdf(ReportRequest $request, ReportService $service): Response
    {
        [$period, $date] = $this->parameters($request);
        $report = $service->build($request->user(), $period, $date);

        return Pdf::loadView('reports.pdf', ['report' => $report])
            ->setPaper('a4', 'portrait')
            ->download('laporan-'.$period->value.'-'.$date->format('Y-m-d').'.pdf');
    }

    public function excel(ReportRequest $request, ReportService $service): BinaryFileResponse
    {
        [$period, $date] = $this->parameters($request);
        $report = $service->build($request->user(), $period, $date);

        return Excel::download(
            new ReportExport($report),
            'laporan-'.$period->value.'-'.$date->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * @return array{0: ReportPeriod, 1: Carbon}
     */
    private function parameters(ReportRequest $request): array
    {
        $period = ReportPeriod::tryFrom((string) $request->input('period', '')) ?? ReportPeriod::Monthly;

        $date = $request->filled('date')
            ? Carbon::createFromFormat('!Y-m-d', (string) $request->input('date'))
            : now();

        return [$period, $date];
    }
}
