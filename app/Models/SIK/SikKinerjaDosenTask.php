<?php

namespace App\Models\SIK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikKinerjaDosenTask extends Model
{
    use HasFactory;
    protected $table = 'sik_kinerja_dosen_tasks';
    protected $connection = "mysql_second";
}
