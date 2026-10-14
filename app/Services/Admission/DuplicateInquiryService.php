<?php

namespace App\Services\Admission;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable duplicate-inquiry check (admission_setting.duplicate_inquiry_match: none | mobile | email | mobile_name).
 * It is a warning, not a hard block: the first matching submit is refused with the matching enquiry number and
 * the same details submitted again are accepted, so a genuine second child with the same parent mobile is not stuck.
 */
class DuplicateInquiryService
{
    public function __construct(protected SettingService $settings)
    {
    }

    /** A warning message if the submission looks like a duplicate and has not been acknowledged yet, else null. */
    public function warning(int $instituteId, array $data, ?int $syear = null): ?string
    {
        if (! Schema::hasTable('admission_setting')) {
            return null; // migrations not run yet: behave exactly as before
        }
        $rule = $this->settings->get($instituteId, 'duplicate_inquiry_match');
        $mobile = trim((string) ($data['mobile'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $first = strtolower(trim((string) ($data['first_name'] ?? '')));

        $query = DB::table('admission_enquiry')->where('sub_institute_id', $instituteId)->when($syear, fn ($q) => $q->where('syear', $syear));
        switch ($rule) {
            case 'mobile':
                if ($mobile === '') {
                    return null;
                }
                $query->where('mobile', $mobile);
                break;
            case 'email':
                if ($email === '') {
                    return null;
                }
                $query->whereRaw('LOWER(email) = ?', [$email]);
                break;
            case 'mobile_name':
                if ($mobile === '' || $first === '') {
                    return null;
                }
                $query->where('mobile', $mobile)->whereRaw('LOWER(first_name) = ?', [$first]);
                break;
            default:
                return null;
        }

        $existing = $query->orderBy('id')->first(['id', 'enquiry_no', 'first_name', 'last_name']);
        if (! $existing) {
            return null;
        }

        $key = sha1(implode('|', [$instituteId, $rule, $mobile, $email, $first]));
        if (session()->get('admission_dup_ack') === $key) {
            session()->forget('admission_dup_ack');

            return null; // same details submitted again: the user has seen the warning
        }
        session()->put('admission_dup_ack', $key);

        return 'Possible duplicate: enquiry no. '.$existing->enquiry_no.' ('.trim($existing->first_name.' '.$existing->last_name)
            .') already has the same '.str_replace('_', ' + ', $rule).'. Nothing was saved. Submit the same details again to create it anyway.';
    }
}
