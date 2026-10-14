<?php

namespace App\Services\Placement;

use App\Models\placement\PlacementCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Company state machine: Prospect -> Invited -> Confirmed -> Visited, with Blacklisted from any state. */
class CompanyService
{
    public const TRANSITIONS = [
        'prospect'    => ['invited', 'blacklisted'],
        'invited'     => ['confirmed', 'prospect', 'blacklisted'],
        'confirmed'   => ['visited', 'invited', 'blacklisted'],
        'visited'     => ['invited', 'blacklisted'], // invited again for the next season
        'blacklisted' => ['prospect'],
    ];

    protected AuditService $audit;

    public function __construct(AuditService $audit)
    {
        $this->audit = $audit;
    }

    public function allowedNext(PlacementCompany $company): array
    {
        return self::TRANSITIONS[$company->status] ?? [];
    }

    public function transition(PlacementCompany $company, string $to, ?string $reason = null): PlacementCompany
    {
        $from = $company->status;

        if (! in_array($to, $this->allowedNext($company), true)) {
            throw ValidationException::withMessages([
                'status' => "A company cannot move from {$from} to {$to}.",
            ]);
        }

        // Entering or leaving the blacklist is a policy decision and must be explained.
        if (($to === 'blacklisted' || $from === 'blacklisted') && trim((string) $reason) === '') {
            throw ValidationException::withMessages(['reason' => 'A reason is required to change the blacklist status.']);
        }

        return DB::transaction(function () use ($company, $from, $to, $reason) {
            $company->status = $to;
            $company->blacklist_reason = $to === 'blacklisted' ? $reason : null;
            $company->save();

            $this->audit->log('company', $company->id, 'status_change', ['status' => $from], ['status' => $to], $reason);

            return $company;
        });
    }
}
