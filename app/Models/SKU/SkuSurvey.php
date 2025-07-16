<?php

namespace App\Models\SKU;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        "nama_id",
        "kode_unit_id",
        "level_survey_id",
        "komentar"
    ];
}
