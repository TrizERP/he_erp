<?php

namespace App\Models\placement;

class PlacementNotificationLog extends PlacementPlainModel
{
    protected $table = 'placement_notification_log';

    protected $casts = ['sent_at' => 'datetime', 'read_at' => 'datetime'];
}
