<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\RetentionPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RetentionPolicyController extends Controller
{
    public function index(): View
    {
        return view('admin.retention.index', [
            'policies' => RetentionPolicy::query()->orderByDesc('is_active')->orderBy('nama')->get(),
            'archivedParticipants' => Participant::withTrashed()
                ->where(function ($query): void {
                    $query->whereNotNull('archived_at')->orWhereNotNull('deleted_at');
                })
                ->orderByDesc('archived_at')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.retention.form', [
            'policy' => new RetentionPolicy([
                'entity_type' => 'participants',
                'basis' => 'tanggal_selesai_batch',
                'retention_years' => 5,
                'action' => 'cold_storage',
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $policy = DB::transaction(function () use ($request): RetentionPolicy {
            $policy = RetentionPolicy::create($this->validatedPolicy($request));
            $this->setActivePolicy($policy);

            return $policy;
        });

        return redirect()->route('admin.retention.index')->with('status', 'Kebijakan retensi berhasil ditambahkan.');
    }

    public function edit(RetentionPolicy $policy): View
    {
        abort_unless($policy->entity_type === 'participants', 404);

        return view('admin.retention.form', compact('policy'));
    }

    public function update(Request $request, RetentionPolicy $policy): RedirectResponse
    {
        abort_unless($policy->entity_type === 'participants', 404);

        DB::transaction(function () use ($request, $policy): void {
            $policy->update($this->validatedPolicy($request, $policy));
            $this->setActivePolicy($policy);
        });

        return redirect()->route('admin.retention.index')->with('status', 'Kebijakan retensi berhasil diperbarui.');
    }

    public function restoreParticipant(int $participant): RedirectResponse
    {
        $record = Participant::withTrashed()->findOrFail($participant);
        $record->restore();
        $record->forceFill([
            'archived_at' => null,
            'storage_tier' => 'hot',
        ])->save();

        return redirect()->route('admin.retention.index')->with('status', 'Data alumni berhasil dipulihkan.');
    }

    /**
     * @return array{nama: string, entity_type: string, basis: string, retention_years: int, action: string, is_active: bool, created_by: int|null}
     */
    private function validatedPolicy(Request $request, ?RetentionPolicy $policy = null): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120', Rule::unique('retention_policies', 'nama')->ignore($policy?->id)],
            'retention_years' => ['required', 'integer', 'min:1', 'max:100'],
            'action' => ['required', Rule::in(['arsipkan', 'cold_storage', 'hapus_lembut'])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return [
            'nama' => $validated['nama'],
            'entity_type' => 'participants',
            'basis' => 'tanggal_selesai_batch',
            'retention_years' => $validated['retention_years'],
            'action' => $validated['action'],
            'is_active' => $request->boolean('is_active'),
            'created_by' => $policy?->created_by ?? $request->user()->id,
        ];
    }

    private function setActivePolicy(RetentionPolicy $policy): void
    {
        if (! $policy->is_active) {
            return;
        }

        DB::table('retention_policies')
            ->where('entity_type', 'participants')
            ->where('id', '!=', $policy->id)
            ->update(['is_active' => false]);
    }
}
