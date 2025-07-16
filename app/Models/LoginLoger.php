<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLoger extends Model
{
    use HasFactory;

    // Uncomment this when production
    // protected $connection = "pgsql_dragon_4rch1";

    // Uncomment this when Development
    // protected $connection = "mysql";

    protected $table = "sso_login_logger";

    protected $connection;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Dynamically set the database connection
        $this->connection = env('APP_ENV') === 'local' ? 'mysql' : 'pgsql_dragon_4rch1';
    }
}
