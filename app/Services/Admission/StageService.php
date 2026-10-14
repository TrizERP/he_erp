<?php

namespace App\Services\Admission;

/**
 * Turns the raw workflow columns into plain-language guidance: "where is this application now" and
 * "what should the user do next". One definition used by the lists, the dashboard and the application page,
 * so every screen tells the same story. Pure functions, no database access.
 *
 * Expected keys on the row (array or object): form_count, application_status, eligibility_status, selection_status,
 * confirmation_status, student_id, docs_to_verify, last_offer.
 */
class StageService
{
    public const STEPS = [
        'application' => ['Application', 'sec-application'],
        'documents'   => ['Documents', 'sec-documents'],
        'eligibility' => ['Eligibility', 'sec-eligibility'],
        'selection'   => ['Selection', 'sec-selection'],
        'offer'       => ['Offer', 'sec-offer'],
        'fee'         => ['Fee', 'sec-fee'],
        'admitted'    => ['Admitted', 'sec-confirm'],
    ];

    /** @return array{label:string,color:string,next:string,anchor:string,tone:string} tone: todo | done | stop */
    public function describe($row): array
    {
        $g = fn (string $key) => is_array($row) ? ($row[$key] ?? null) : ($row->$key ?? null);

        $confirmation = $g('confirmation_status');
        $selection = $g('selection_status');
        $offer = $g('last_offer');

        if ($confirmation === 'confirmed') {
            return $this->out('Admitted', 'success', 'Nothing more to do. The student record has been created.', 'sec-confirm', 'done');
        }
        if ($confirmation === 'cancelled') {
            return $this->out('Cancelled', 'danger', 'This admission was cancelled. An administrator can resume it if needed.', 'sec-confirm', 'stop');
        }
        if (! (int) $g('form_count')) {
            return $this->out('Inquiry only', 'default', 'Convert the inquiry into an application.', 'sec-application', 'todo');
        }
        if (in_array($g('application_status'), ['draft', 'reopened'], true)) {
            return $this->out($g('application_status') === 'reopened' ? 'Reopened for correction' : 'Application in draft', 'warning',
                'Complete the details and documents, then submit the application.', 'sec-application', 'todo');
        }
        if ($selection === 'rejected') {
            return $this->out('Rejected', 'danger', 'No action needed. You can move it back to review if the decision changes.', 'sec-selection', 'stop');
        }
        if ((int) $g('docs_to_verify') > 0) {
            return $this->out('Documents to verify', 'info', 'Open the documents, then verify them or ask for a re-upload.', 'sec-documents', 'todo');
        }
        if ($g('eligibility_status') === 'ineligible') {
            return $this->out('Not eligible', 'danger', 'Does not meet the eligibility criteria. Only an administrator can override this, with a reason.', 'sec-eligibility', 'stop');
        }
        if (in_array($g('eligibility_status'), ['pending_review', 'needs_review'], true)) {
            return $this->out($g('eligibility_status') === 'needs_review' ? 'Needs a person to decide' : 'Eligibility not checked', 'warning',
                'Run the eligibility check and look at the result.', 'sec-eligibility', 'todo');
        }
        if (in_array($selection, [null, '', 'under_review'], true)) {
            return $this->out('Awaiting selection decision', 'info', 'Decide: shortlist, waitlist, select or reject.', 'sec-selection', 'todo');
        }
        if (in_array($selection, ['shortlisted', 'waitlisted'], true)) {
            return $this->out(ucfirst($selection), 'info', 'Make the final decision: select or reject.', 'sec-selection', 'todo');
        }
        if ($offer === 'issued') {
            return $this->out('Offer sent', 'primary', 'Waiting for the applicant to reply. Record the reply when you get it.', 'sec-offer', 'todo');
        }
        if ($offer === 'declined') {
            return $this->out('Offer declined', 'warning', 'The applicant declined. Issue another offer or close the application.', 'sec-offer', 'stop');
        }
        if (in_array($offer, ['expired', 'withdrawn'], true)) {
            return $this->out('Offer '.$offer, 'warning', 'Issue a new offer.', 'sec-offer', 'todo');
        }
        if ($offer === 'accepted') {
            return $this->out('Offer accepted', 'success', 'Check the fee and the checklist, then confirm the admission.', 'sec-confirm', 'todo');
        }
        if ($confirmation === 'on_hold') {
            return $this->out('On hold', 'warning', 'Resolve the reason it was put on hold, then confirm.', 'sec-confirm', 'todo');
        }

        return $this->out('Selected', 'success', 'Issue an offer, or confirm the admission if offers are not used.', 'sec-offer', 'todo');
    }

    /**
     * Progress bar for the application page.
     *
     * @param  array  $facts  keys: form, submitted, docs_ok, eligible, selected, offer_accepted, fee_ok, admitted (all bool)
     * @return array<int,array{key:string,label:string,anchor:string,state:string}> state: done | current | todo
     */
    public function steps(array $facts): array
    {
        $done = [
            'application' => (bool) ($facts['form'] ?? false) && (bool) ($facts['submitted'] ?? false),
            'documents'   => (bool) ($facts['docs_ok'] ?? false),
            'eligibility' => (bool) ($facts['eligible'] ?? false),
            'selection'   => (bool) ($facts['selected'] ?? false),
            'offer'       => (bool) ($facts['offer_accepted'] ?? false),
            'fee'         => (bool) ($facts['fee_ok'] ?? false),
            'admitted'    => (bool) ($facts['admitted'] ?? false),
        ];

        $steps = [];
        $currentSet = false;
        foreach (self::STEPS as $key => [$label, $anchor]) {
            $state = 'done';
            if (! $done[$key]) {
                $state = $currentSet ? 'todo' : 'current';
                $currentSet = true;
            }
            $steps[] = ['key' => $key, 'label' => $label, 'anchor' => $anchor, 'state' => $state];
        }

        return $steps;
    }

    protected function out(string $label, string $color, string $next, string $anchor, string $tone): array
    {
        return ['label' => $label, 'color' => $color, 'next' => $next, 'anchor' => $anchor, 'tone' => $tone];
    }
}
