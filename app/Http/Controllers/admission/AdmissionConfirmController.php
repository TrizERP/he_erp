<?php

namespace App\Http\Controllers\admission;

use App\Http\Requests\admission\ConfirmationStatusRequest;
use App\Http\Requests\admission\ConfirmRequest;
use App\Services\Admission\AdmissionAccess;
use App\Services\Admission\AdmissionException;
use App\Services\Admission\ConfirmationService;

/** Confirmation: transactional + idempotent confirm (with audited override) and hold / cancel / resume. */
class AdmissionConfirmController extends AdmissionController
{
    public function __construct(protected ConfirmationService $confirmation)
    {
    }

    public function confirm(ConfirmRequest $request, int $enquiry)
    {
        $override = trim((string) $request->input('override_reason'));
        try {
            $result = $this->confirmation->confirm(
                $this->instituteId(), $enquiry, (int) ($this->syear() ?? 0), session()->get('term_id'),
                $override !== '' ? $override : null, AdmissionAccess::can('override')
            );
        } catch (AdmissionException $e) {
            return $this->flash(0, $e->getMessage(), $e->details);
        }

        $message = match ($result['status']) {
            'confirmed'         => 'Admission confirmed. Student ID '.$result['student_id'].', enrollment no. '.$result['enrollment_no'].'.',
            'already_confirmed' => 'Already confirmed. Student ID '.$result['student_id'].', enrollment no. '.$result['enrollment_no'].' (no duplicate created).',
            default             => 'Linked to existing student ID '.$result['student_id'].' (no duplicate created).',
        };

        return $this->flash(1, $message);
    }

    public function status(ConfirmationStatusRequest $request, int $enquiry)
    {
        return $this->attempt(
            fn () => $this->confirmation->setStatus($this->instituteId(), $enquiry, $request->input('status'), $request->input('reason'), AdmissionAccess::can('override')),
            'Admission status updated.'
        );
    }
}
