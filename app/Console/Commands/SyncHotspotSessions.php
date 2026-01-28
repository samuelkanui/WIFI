<?php

namespace App\Console\Commands;

use App\Services\MikroTikService;
use Illuminate\Console\Command;

class SyncHotspotSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotspot:sync-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync active hotspot sessions from MikroTik router to database';

    /**
     * Execute the console command.
     */
    public function handle(MikroTikService $mikrotik)
    {
        $this->info('Starting hotspot session sync...');

        try {
            $mikrotik->syncSessions();
            $this->info('Sessions synced successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to sync sessions: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
