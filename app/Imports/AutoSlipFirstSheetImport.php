<?php

namespace App\Imports;

use App\Imports\AutoSlipImport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AutoSlipFirstSheetImport implements WithMultipleSheets
{

    public function sheets(): array
    {
        return [
            0 => new OneSheet(),
        ];
    }
}
