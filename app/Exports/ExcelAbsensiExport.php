<?php

namespace App\Exports;

use App\Models\SIK\SikBiodata;
use App\Models\SIK\SikExtraBiodata;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;

class ExcelAbsensiExport implements WithStyles, ShouldAutoSize, WithColumnFormatting, FromCollection, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            2 => ['font' => ['size' => 12]],

            // Styling a specific cell by coordinate.
            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }

    public function headings(): array
    {
        return [
            [
                'No', 'Nama', 'NIK', 'Hadir', 'Terlambat', 'Tidak Hadir'
            ],
            [
                'rn_no',
                'rn_nama',
                'rn_nik',
                'rn_abs_hadir',
                'rn_abs_terlambat',
                'rn_abs_tidak_hadir',
            ], // Actual data headers
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
