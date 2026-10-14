<?php

namespace App\Models\admission;

use Illuminate\Database\Eloquent\Model;

/**
 * Workflow state for one legacy admission_enquiry row. Every status / decision / student-link /
 * confirmation column is intentionally NOT fillable: they are only set by the Admission services.
 */
class AdmissionWorkflow extends Model
{
    public const INQUIRY = ['new' => 'New', 'contacted' => 'Contacted', 'follow_up' => 'Follow-up', 'converted' => 'Converted', 'closed' => 'Closed'];
    public const ELIGIBILITY = ['pending_review' => 'Pending Review', 'eligible' => 'Eligible', 'ineligible' => 'Ineligible', 'needs_review' => 'Needs Review'];
    public const SELECTION = ['under_review' => 'Under Review', 'shortlisted' => 'Shortlisted', 'waitlisted' => 'Waitlisted', 'rejected' => 'Rejected', 'selected' => 'Selected'];
    public const CONFIRMATION = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'on_hold' => 'On Hold', 'cancelled' => 'Cancelled'];

    protected $table = 'admission_workflow';
    protected $fillable = ['sub_institute_id', 'enquiry_id', 'syear', 'standard_id'];
    protected $casts = [
        'eligibility_values'    => 'array',
        'eligibility_override'  => 'boolean',
        'fee_waived'            => 'boolean',
        'confirmation_override' => 'boolean',
        'submitted_at'          => 'datetime',
        'confirmed_at'          => 'datetime',
    ];
}
