<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GraduationReportExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, object>  $rows
     */
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Program', 'Periode (Tahun)', 'Total Peserta', 'Lulus', 'Tidak Lulus', 'Belum Dinilai'];
    }

    /**
     * @param  object  $row
     * @return array<int, int|string>
     */
    public function map($row): array
    {
        return [
            $row->program,
            $row->periode,
            $row->total_peserta,
            $row->lulus,
            $row->tidak_lulus,
            $row->belum_dinilai,
        ];
    }
}
