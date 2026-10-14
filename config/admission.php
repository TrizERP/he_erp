<?php

return [
    // Private disk for applicant documents. Never 'public': files are streamed through an authorised route.
    'disk' => env('ADMISSION_DISK', 'local'),

    'upload' => [
        'default_mimes' => 'pdf,jpg,jpeg,png',
        'default_max_kb' => 2048,
        'hard_max_kb' => 10240, // ceiling even if a requirement is configured higher
        'allowed_mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
    ],

    // Profile-name keywords (tbluserprofilemaster.name, lower-cased) that map to admission roles.
    // First match wins in the order: admin, approver, accounts, officer.
    'role_keywords' => [
        'admin'    => ['admin'],
        'officer'  => ['admission'],
        'accounts' => ['account', 'fee'],
        'approver' => ['approver', 'committee', 'principal', 'director'],
    ],

    // Per-institute settings (admission_setting). Defaults deliberately preserve the pre-existing
    // behaviour: nothing blocks confirmation until the institute turns a rule on.
    'settings' => [
        'require_documents_verified' => '0',   // all mandatory documents must be verified
        'require_eligibility'        => '0',   // eligibility must be "eligible" (ineligible always blocks)
        'require_selection'          => '0',   // selection must be "selected"
        'require_offer_accepted'     => '0',   // an accepted offer must exist
        'fee_condition'              => 'none', // none | any_payment | full
        'allow_fee_waiver'           => '0',
        'duplicate_inquiry_match'    => 'none',   // none | mobile | email | mobile_name (none = legacy behaviour)
    ],

    // Applicant attributes an eligibility rule may test (column => source table alias).
    'eligibility_fields' => [
        'percentage'           => 'Previous percentage (enquiry)',
        'last_exam_percentage' => 'Last exam percentage (form)',
        'category'             => 'Category',
        'gender'               => 'Gender',
        'age'                  => 'Age',
        'annual_income'        => 'Annual income',
        'religion'             => 'Religion',
        'nationality'          => 'Nationality',
        'previous_standard'    => 'Previous standard',
    ],

    // Keys masked before values are written to the audit trail.
    'sensitive_keys' => ['password', 'otp', 'mobile', 'email', 'aadharcard_number', 'aadhar_number'],
];
