<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class SyncBlockedIps extends Command
{
    
    protected $signature = 'defread:sync-blocked';
    protected $description = 'Sync blocked IPs from definitiy database to Redis';

    public function handle()
    {
        $ips = DB::connection('mysql_dfiy')
            ->table('definity_ips_blacklists')
            ->where('block', 1)
            ->pluck('ip_address')
            ->toArray();

        Redis::del('blocked_ips'); // Clear existing
        foreach ($ips as $ip) {
            Redis::sadd('blocked_ips', $ip);
        }

        $this->info('Synced ' . count($ips) . ' blocked IPs with Definity Blacklist....');
    }
}


