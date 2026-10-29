<?php

namespace App\Http\Controllers;

use App\Exports\GraduationReportExport;
use App\Models\Enrollment;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class GraduationReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $rows = $this->reportRows($filters);

        return view('admin.reports.graduation', [
            'rows' => $rows,
            'filters' => $filters,
        ]);
    }

    public function export(Request $request, string $format): BinaryFileResponse|Response
    {
        abort_unless(in_array($format, ['xlsx', 'pdf'], true), 404);

        $filters = $this->validatedFilters($request);
        $rows = $this->reportRows($filters);
        $filename = 'rekap-kelulusan-'.now()->format('Ymd-His');

        AuditLogger::record('EXPORT', null, null, [
            'report' => 'graduation',
            'format' => $format,
            'filters' => $filters,
            'rows' => $rows->count(),
        ]);

        if ($format === 'xlsx') {
            return Excel::download(new GraduationReportExport($rows), $filename.'.xlsx');
        }

        return Pdf::loadView('admin.reports.graduation-pdf', [
            'rows' => $rows,
            'filters' => $filters,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->download($filename.'.pdf');
    }

    /**
     * @param  array{from: string|null, to: string|null}  $filters
     * @return Collection<int, object>
     */
    private function reportRows(array $filters): Collection
    {
        return $this->completedEnrollments()
            ->selectRaw('programs.id AS program_id, programs.nama AS program, batches.tahun AS periode')
            ->selectRaw('COUNT(enrollments.id) AS total_peserta')
            ->selectRaw("SUM(CASE WHEN enrollments.status_kelulusan = 'lulus' THEN 1 ELSE 0 END) AS lulus")
            ->selectRaw("SUM(CASE WHEN enrollments.status_kelulusan = 'tidak_lulus' THEN 1 ELSE 0 END) AS tidak_lulus")
            ->selectRaw("SUM(CASE WHEN enrollments.status_kelulusan = 'belum_dinilai' THEN 1 ELSE 0 END) AS belum_dinilai")
            ->when($filters['from'], fn (Builder $query, string $date) => $query->whereDate('batches.tanggal_selesai', '>=', $date))
            ->when($filters['to'], fn (Builder $query, string $date) => $query->whereDate('batches.tanggal_selesai', '<=', $date))
            ->groupBy('programs.id', 'programs.nama', 'batches.tahun')
            ->orderBy('programs.nama')
            ->orderByDesc('batches.tahun')
            ->get();
    }

    /**
     * @return Builder<Enrollment>
     */
    private function completedEnrollments(): Builder
    {
        return Enrollment::query()
            ->join('batches', 'batches.id', '=', 'enrollments.batch_id')
            ->join('programs', 'programs.id', '=', 'batches.program_id')
            ->join('participants', 'participants.id', '=', 'enrollments.participant_id')
            ->where('batches.status', 'selesai')
            ->whereNotNull('batches.tanggal_selesai')
            ->whereNull('batches.deleted_at')
            ->whereNull('participants.deleted_at');
    }

    /**
     * @return array{from: string|null, to: string|null}
     */
    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date', 'required_with:to', Rule::when($request->filled('to'), ['before_or_equal:to'])],
            'to' => ['nullable', 'date', 'required_with:from', Rule::when($request->filled('from'), ['after_or_equal:from'])],
        ]);

        return [
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
        ];
    }
}
