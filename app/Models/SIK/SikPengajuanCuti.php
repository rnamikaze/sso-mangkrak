<?php

namespace App\Models\SIK;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SikPengajuanCuti extends Model
{
    use HasFactory;

    protected $connection = "mysql_second";
    protected $table = "sik_pengajuan_cuti";

    protected $fillable = [
        'cuti_type',
        'cuti_date_arr',
        'pengajuan_type',
        'id_pegawai_penugasan',
        'komentar',
        'bukti_arr',
        'status_pengajuan',
        'active'
    ];
}
