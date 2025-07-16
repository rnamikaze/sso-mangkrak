<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AllowedAppArrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the table name
        $tableName = 'users'; // Change this to your actual table name

        // Fetch all rows
        $records = DB::table($tableName)->get();

        foreach ($records as $record) {
            if ($record->username == "superadmin") {
                echo "SKIPPED " . $record->name . "\r\n";
                continue;
            }

            // Unserialize the column data
            $allowedApps = unserialize($record->allowed_app_arr);

            // Check if 'sku' is not present
            if (!in_array('sik', $allowedApps)) {
                $allowedApps[] = 'sik'; // Add 'sku' to the array

                // Update the record in the database
                DB::table($tableName)
                    ->where('id', $record->id)
                    ->update(['allowed_app_arr' => serialize($allowedApps)]);

                echo "successfully modified " . $record->name . "\r\n";
            }
        }
    }
}
