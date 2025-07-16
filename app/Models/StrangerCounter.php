<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrangerCounter extends Model
{
    use HasFactory;

    protected $table = "sso_stranger_counters";

    // Uncomment this when production
    // protected $connection = "pgsql_dragon_4rch1";

    // Uncomment this when Development
    // protected $connection = "mysql";

    protected $fillable = [
        "ip_address",
        "user_agent",
        "browser",
        "device_type"
    ];

    protected $connection;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Dynamically set the database connection
        $this->connection = env('APP_ENV') === 'local' ? 'mysql' : 'pgsql_dragon_4rch1';
    }
}
