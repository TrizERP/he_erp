<?php

namespace App\Models\placement;

class PlacementTemplate extends PlacementModel
{
    protected $table = 'placement_template';

    public const CHANNELS = ['email' => 'Email', 'whatsapp' => 'WhatsApp', 'inapp' => 'In-app'];
    public const PLACEHOLDERS = ['{student_name}', '{company}', '{date}', '{role}', '{venue}', '{ctc}'];

}
