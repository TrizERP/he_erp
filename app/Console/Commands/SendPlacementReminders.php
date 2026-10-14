<?php

namespace App\Console\Commands;

use App\Services\Placement\DriveService;
use Illuminate\Console\Command;

class SendPlacementReminders extends Command
{
    protected $signature = 'placement:send-reminders';

    protected $description = 'Remind eligible students who have not applied that a placement drive registration deadline is near';

    public function handle(DriveService $drives): int
    {
        $this->info('Reminders sent for '.$drives->sendDeadlineReminders().' drive(s).');

        return self::SUCCESS;
    }
}
