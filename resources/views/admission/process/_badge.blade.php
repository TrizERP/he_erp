@php
    // value => [label shown to people, bootstrap colour]. Plain words instead of internal status codes.
    $badgeMap = [
        'new' => ['New inquiry', 'default'], 'contacted' => ['Contacted', 'info'], 'follow_up' => ['Follow-up needed', 'warning'],
        'converted' => ['Application created', 'success'], 'closed' => ['Closed', 'default'],
        'draft' => ['Draft (not submitted)', 'default'], 'submitted' => ['Submitted', 'success'], 'reopened' => ['Reopened for correction', 'warning'],
        'pending_review' => ['Not checked yet', 'default'], 'eligible' => ['Eligible', 'success'], 'ineligible' => ['Not eligible', 'danger'],
        'needs_review' => ['Needs a person to decide', 'warning'],
        'under_review' => ['Under review', 'info'], 'shortlisted' => ['Shortlisted', 'info'], 'waitlisted' => ['Waitlisted', 'warning'],
        'rejected' => ['Rejected', 'danger'], 'selected' => ['Selected', 'success'],
        'pending' => ['Not done yet', 'default'], 'uploaded' => ['Uploaded - waiting for check', 'info'], 'verified' => ['Verified', 'success'],
        'resubmission_required' => ['Please upload again', 'warning'],
        'issued' => ['Offer sent', 'primary'], 'accepted' => ['Accepted', 'success'], 'declined' => ['Declined', 'danger'],
        'expired' => ['Expired', 'default'], 'withdrawn' => ['Withdrawn', 'default'],
        'unpaid' => ['Not paid', 'danger'], 'partially_paid' => ['Part paid', 'warning'], 'paid' => ['Paid', 'success'], 'waived' => ['Waived', 'info'],
        'confirmed' => ['Admitted', 'success'], 'on_hold' => ['On hold', 'warning'], 'cancelled' => ['Cancelled', 'danger'],
    ];
    $badge = $badgeMap[$value ?? ''] ?? [ucwords(str_replace('_', ' ', (string) ($value ?? ''))), 'default'];
@endphp
@if (isset($value) && $value !== null && $value !== '')
    <span class="label label-{{ $badge[1] }}">{{ $badge[0] }}</span>
@else
    <span class="text-muted">-</span>
@endif
