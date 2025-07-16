<?php

namespace App\Imports\SIK;

use App\Imports\OneSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class PresensiFirst implements WithMultipleSheets, WithStartRow, WithCalculatedFormulas
{
    /**
     * @param Collection $collection
     */
    public function sheets(): array
    {
        return [
            0 => new OneSheet(),
        ];
    }

    public function startRow(): int
    {
        return 1;
    }
}
