<?php

namespace App\Console\Commands;

use App\Services\Placement\DriveService;
use Illuminate\Console\Command;

class ClosePlacementRegistrations extends Command
{
    protected $signature = 'placement:close-registrations';

    protected $description = 'Close registration on placement drives whose registration deadline has passed';

    public function handle(DriveService $drives): int
    {
        $closed = $drives->closeExpiredRegistrations();
        $this->info("Closed registration on {$closed} drive(s).");

        return self::SUCCESS;
    }
}
