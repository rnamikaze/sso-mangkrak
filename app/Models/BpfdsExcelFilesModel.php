<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BpfdsExcelFilesModel extends Model
{
    use HasFactory;

    protected $table = "table_bpdfs_excel_files";
    protected $connection = "mysql_second";
}
