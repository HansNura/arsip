<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\BatchStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\GraduationStatus;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\Program;
use App\Models\TrainingSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function programs(): View
    {
        return view('admin.programs.index', [
            'programs' => Program::withCount('batches')->orderBy('nama')->paginate(15),
        ]);
    }

    public function createProgram(): View
    {
        return view('admin.programs.form', ['program' => new Program]);
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        Program::create($this->validateProgram($request));

        return redirect()->route('admin.programs.index')->with('status', 'Program pelatihan berhasil ditambahkan.');
    }

    public function editProgram(Program $program): View
    {
        return view('admin.programs.form', compact('program'));
    }

    public function updateProgram(Request $request, Program $program): RedirectResponse
    {
        $program->update($this->validateProgram($request, $program));

        return redirect()->route('admin.programs.index')->with('status', 'Program pelatihan berhasil diperbarui.');
    }

    public function destroyProgram(Program $program): RedirectResponse
    {
        if ($program->batches()->exists()) {
            return back()->withErrors([
                'program' => 'Program tidak dapat diarsipkan selama masih memiliki angkatan.',
            ]);
        }

        $program->delete();

        return redirect()->route('admin.programs.index')->with('status', 'Program pelatihan berhasil diarsipkan.');
    }

    public function batches(): View
    {
        return view('admin.batches.index', [
            'batches' => Batch::with('program')
                ->withCount('enrollments')
                ->orderByDesc('tahun')
                ->orderBy('nama')
                ->paginate(15),
        ]);
    }

    public function createBatch(): View
    {
        return view('admin.batches.form', [
            'batch' => new Batch,
            'programs' => Program::where('is_active', true)->orderBy('nama')->get(),
            'statuses' => BatchStatus::cases(),
        ]);
    }

    public function storeBatch(Request $request): RedirectResponse
    {
        Batch::create($this->validateBatch($request));

        return redirect()->route('admin.batches.index')->with('status', 'Angkatan berhasil ditambahkan.');
    }

    public function editBatch(Batch $batch): View
    {
        return view('admin.batches.form', [
            'batch' => $batch,
            'programs' => Program::orderBy('nama')->get(),
            'statuses' => BatchStatus::cases(),
        ]);
    }

    public function updateBatch(Request $request, Batch $batch): RedirectResponse
    {
        $batch->update($this->validateBatch($request, $batch));

        return redirect()->route('admin.batches.index')->with('status', 'Angkatan berhasil diperbarui.');
    }

    public function destroyBatch(Batch $batch): RedirectResponse
    {
        if ($batch->enrollments()->withTrashed()->exists()) {
            return back()->withErrors([
                'batch' => 'Angkatan tidak dapat diarsipkan selama masih memiliki peserta terdaftar.',
            ]);
        }

        $batch->delete();

        return redirect()->route('admin.batches.index')->with('status', 'Angkatan berhasil diarsipkan.');
    }

    public function showBatch(Batch $batch): View
    {
        $batch->load([
            'program',
            'trainingSessions' => fn ($query) => $query->orderBy('pertemuan_ke'),
            'enrollments' => fn ($query) => $query->with([
                'participant',
                'attendance',
            ])->orderBy('created_at'),
        ]);

        return view('admin.batches.show', [
            'batch' => $batch,
            'enrollmentStatuses' => EnrollmentStatus::cases(),
            'attendanceStatuses' => AttendanceStatus::cases(),
        ]);
    }

    public function storeEnrollment(Request $request, Batch $batch): RedirectResponse
    {
        $data = $request->validate([
            'nik' => ['required', 'digits:16'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:30'],
            'tanggal_daftar' => ['required', 'date'],
            'status_pendaftaran' => ['required', Rule::enum(EnrollmentStatus::class)],
        ]);

        $participant = Participant::withTrashed()->where('nik', $data['nik'])->first();

        if ($participant && $batch->enrollments()->where('participant_id', $participant->id)->exists()) {
            return back()->withErrors(['nik' => 'Peserta ini sudah terdaftar pada angkatan tersebut.'])->withInput();
        }

        if ($batch->kuota !== null && $batch->enrollments()->count() >= $batch->kuota) {
            return back()->withErrors(['nik' => 'Kuota angkatan sudah terpenuhi.'])->withInput();
        }

        DB::transaction(function () use ($participant, $data, $batch): void {
            $participant ??= Participant::create([
                'nik' => $data['nik'],
                'nama_lengkap' => $data['nama_lengkap'],
                'email' => $data['email'] ?? null,
                'no_telepon' => $data['no_telepon'] ?? null,
            ]);

            if ($participant->trashed()) {
                $participant->restore();
            }

            $batch->enrollments()->create([
                'participant_id' => $participant->id,
                'tanggal_daftar' => $data['tanggal_daftar'],
                'status_pendaftaran' => $data['status_pendaftaran'],
            ]);
        });

        return back()->with('status', 'Pendaftaran peserta berhasil disimpan.');
    }

    public function storeSession(Request $request, Batch $batch): RedirectResponse
    {
        $data = $request->validate([
            'pertemuan_ke' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('training_sessions', 'pertemuan_ke')->where('batch_id', $batch->id),
            ],
            'tanggal' => ['required', 'date'],
            'topik' => ['nullable', 'string', 'max:255'],
        ]);

        $batch->trainingSessions()->create($data);

        return back()->with('status', 'Pertemuan berhasil ditambahkan.');
    }

    public function updateAttendance(Request $request, Batch $batch, TrainingSession $trainingSession): RedirectResponse
    {
        abort_unless($trainingSession->batch_id === $batch->id, 404);

        $data = $request->validate([
            'attendance' => ['sometimes', 'array'],
            'attendance.*' => ['nullable', Rule::enum(AttendanceStatus::class)],
        ]);
        $attendance = $data['attendance'] ?? [];

        $enrollmentIds = $batch->enrollments()->whereIn('id', array_keys($attendance))->pluck('id');

        abort_unless($enrollmentIds->count() === count($attendance), 422);

        DB::transaction(function () use ($attendance, $enrollmentIds, $trainingSession, $request): void {
            foreach ($enrollmentIds as $enrollmentId) {
                $status = $attendance[$enrollmentId] ?? null;

                if ($status === null) {
                    Attendance::where('enrollment_id', $enrollmentId)
                        ->where('training_session_id', $trainingSession->id)
                        ->delete();

                    continue;
                }

                Attendance::updateOrCreate(
                    ['enrollment_id' => $enrollmentId, 'training_session_id' => $trainingSession->id],
                    ['status' => $status, 'dicatat_oleh' => $request->user()->id],
                );
            }
        });

        return back()->with('status', 'Absensi berhasil disimpan.');
    }

    public function updateScore(Request $request, Batch $batch, Enrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->batch_id === $batch->id, 404);

        $data = $request->validate([
            'nilai_akhir' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $score = $data['nilai_akhir'] ?? null;
        $enrollment->update([
            'nilai_akhir' => $score,
            'status_kelulusan' => $score === null
                ? GraduationStatus::BelumDinilai
                : ($score >= $batch->program->nilai_min_lulus ? GraduationStatus::Lulus : GraduationStatus::TidakLulus),
        ]);

        return back()->with('status', 'Nilai peserta berhasil diperbarui.');
    }

    public function participants(Request $request): View
    {
        $filters = $request->validate([
            'nik' => ['nullable', 'string', 'max:16'],
            'nama' => ['nullable', 'string', 'max:255'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
        ]);

        $enrollments = Enrollment::with(['participant', 'batch.program']);

        if (filled($filters['nik'] ?? null)) {
            $enrollments->whereHas('participant', fn ($query) => $query->where('nik', 'like', '%'.$filters['nik'].'%'));
        }

        if (filled($filters['nama'] ?? null)) {
            $enrollments->whereHas('participant', fn ($query) => $query->where('nama_lengkap', 'like', '%'.$filters['nama'].'%'));
        }

        if (filled($filters['tahun'] ?? null)) {
            $enrollments->whereHas('batch', fn ($query) => $query->where('tahun', $filters['tahun']));
        }

        if (filled($filters['batch_id'] ?? null)) {
            $enrollments->where('batch_id', $filters['batch_id']);
        }

        return view('admin.participants.index', [
            'enrollments' => $enrollments->orderByDesc('tanggal_daftar')->paginate(20)->withQueryString(),
            'filters' => $filters,
            'years' => Batch::query()->select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun'),
            'batches' => Batch::with('program')->orderByDesc('tahun')->orderBy('nama')->get(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProgram(Request $request, ?Program $program = null): array
    {
        return $request->validate([
            'kode_program' => ['required', 'string', 'max:255', Rule::unique('programs', 'kode_program')->ignore($program?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'standar_kompetensi' => ['nullable', 'string'],
            'durasi_jam' => ['nullable', 'integer', 'min:1'],
            'nilai_min_lulus' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBatch(Request $request, ?Batch $batch = null): array
    {
        return $request->validate([
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'kode_batch' => ['required', 'string', 'max:255', Rule::unique('batches', 'kode_batch')->ignore($batch?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'kuota' => ['nullable', 'integer', 'min:1'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(BatchStatus::class)],
        ]);
    }
}
