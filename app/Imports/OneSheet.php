<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;


class OneSheet implements WithStartRow, WithCalculatedFormulas
{
    public function startRow(): int
    {
        return 1;
    }
}
