<?php

namespace App\Services\Admission;

use RuntimeException;

/** A business-rule violation whose message is safe to show to the user. */
class AdmissionException extends RuntimeException
{
    /** @var array Extra structured detail (e.g. the failing checklist items). */
    public array $details = [];

    public static function because(string $message, array $details = []): self
    {
        $e = new self($message);
        $e->details = $details;

        return $e;
    }
}
