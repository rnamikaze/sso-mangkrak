<?php

namespace App\Models\Aslip;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailerCounterModel extends Model
{
    use HasFactory;
    protected $table = "mailer_counter";
    protected $connection = "mysql_second";

    protected $fillable = [
        'daily_counter', 'active'
    ];

    // Manually set the created_at field with a specific timezone
    public function setCreatedAtAttribute($value)
    {
        // Assume $value is a datetime string in UTC timezone
        $nowVal = Carbon::now('GMT-1');
        $timezone = 'Europe/Dublin'; // Set your desired timezone here
        $datetime = new \DateTime($nowVal, new \DateTimeZone('UTC'));
        $datetime->setTimezone(new \DateTimeZone($timezone));
        $this->attributes['created_at'] = $datetime->format('Y-m-d H:i:s');
    }

    // Manually set the created_at field with a specific timezone
    public function setUpdatedAtAttribute($value)
    {
        // Assume $value is a datetime string in UTC timezone
        $nowVal = Carbon::now('GMT-1');
        $timezone = 'Europe/Dublin'; // Set your desired timezone here
        $datetime = new \DateTime($nowVal, new \DateTimeZone('UTC'));
        $datetime->setTimezone(new \DateTimeZone($timezone));
        $this->attributes['updated_at'] = $datetime->format('Y-m-d H:i:s');
    }
}
