<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Default placement masters and notification templates for every institute. Idempotent.
 *
 *   php artisan db:seed --class=PlacementMasterSeeder
 */
class PlacementMasterSeeder extends Seeder
{
    public function run()
    {
        $institutes = DB::table('school_setup')->pluck('Id');

        foreach ($institutes as $instituteId) {
            foreach ($this->masters() as $type => $rows) {
                foreach ($rows as $i => $row) {
                    $row = (array) $row;
                    $name = $row[0];
                    $this->upsert('placement_master', [
                        'sub_institute_id' => $instituteId, 'master_type' => $type, 'name' => $name,
                    ], [
                        'min_ctc'    => $row[1] ?? null,
                        'max_ctc'    => $row[2] ?? null,
                        'sort_order' => $i + 1,
                        'status'     => 1,
                    ]);
                }
            }

            foreach ($this->templates() as [$channel, $event, $name, $subject, $body]) {
                $this->upsert('placement_template', [
                    'sub_institute_id' => $instituteId, 'channel' => $channel, 'event_key' => $event,
                ], ['name' => $name, 'subject' => $subject, 'body' => $body, 'status' => 1]);
            }
        }
    }

    protected function upsert(string $table, array $match, array $values): void
    {
        $query = DB::table($table)->where($match)->whereNull('deleted_at');
        if ($query->exists()) {
            return; // never overwrite what an institute has customised
        }
        DB::table($table)->insert($match + $values + ['created_at' => now(), 'updated_at' => now()]);
    }

    protected function masters(): array
    {
        return [
            'industry'         => ['Information Technology', 'Banking & Finance', 'Manufacturing', 'Consulting', 'Education', 'Healthcare', 'E-commerce', 'Telecom'],
            'job_role'         => ['Software Engineer', 'Associate Engineer', 'Analyst', 'Management Trainee', 'Business Development Executive'],
            'job_type'         => ['Full-time', 'Internship', 'PPO', 'Internship + FTE'],
            'location'         => ['Ahmedabad', 'Bengaluru', 'Hyderabad', 'Mumbai', 'Pune', 'Remote'],
            // name, min LPA, max LPA
            'salary_band'      => [['Below 3 LPA', 0, 3], ['3 - 6 LPA', 3, 6], ['6 - 10 LPA', 6, 10], ['10 - 20 LPA', 10, 20], ['Above 20 LPA', 20, null]],
            'package_category' => [['Mass Recruiter', 0, 4], ['Normal', 4, 8], ['Dream', 8, 15], ['Super Dream', 15, null]],
            'skill'            => ['Java', 'Python', 'SQL', 'JavaScript', 'Communication', 'Problem Solving', 'Data Structures'],
            'round_type'       => ['Aptitude', 'Technical', 'Group Discussion', 'HR', 'Coding', 'Group Task'],
            'document_type'    => ['Resume', 'Offer Letter', '10th Marksheet', '12th Marksheet', 'Photo ID'],
        ];
    }

    protected function templates(): array
    {
        return [
            ['email', 'drive_published', 'New placement drive', 'New drive: {company}',
                "Dear {student_name},\n\n{company} is visiting on {date} for the role of {role}. Registration is now open.\n\nPlacement Cell"],
            ['whatsapp', 'drive_published', 'New placement drive', null,
                'Hi {student_name}, {company} drive on {date}. Register in the placement portal.'],
            ['email', 'round_scheduled', 'Round scheduled', '{round} - {company}',
                "Dear {student_name},

You are scheduled for {round} with {company} on {date} at {venue}.

Placement Cell"],
            ['inapp', 'round_scheduled', 'Round scheduled', null,
                '{company}: {round} on {date} at {venue}.'],
            ['email', 'round_result', 'Round result', 'Result: {round} - {company}',
                "Dear {student_name},

Your result for {round} with {company}: {result}.

Placement Cell"],
            ['inapp', 'deadline_reminder', 'Registration closing', null,
                'Reminder: registration for {company} closes on {deadline}. You have not applied yet.'],
            ['email', 'deadline_reminder', 'Registration closing', 'Last chance: {company}',
                "Dear {student_name},

Registration for the {company} drive closes on {deadline}. You have not applied yet.

Placement Cell"],
            ['inapp', 'offer_issued', 'Offer issued', null,
                'Congratulations! {company} has issued you an offer ({role}, {ctc}). Please respond in My Offers.'],
            ['inapp', 'offer_status', 'Offer update', null,
                '{company}: your offer for {role} is now {status}.'],
            ['inapp', 'announcement', 'Announcement', null,
                '{title}: {notice}'],
            ['inapp', 'round_result', 'Round result', null,
                '{company}: your round result is published.'],
            ['email', 'offer_issued', 'Offer issued', 'Offer from {company}',
                "Dear {student_name},\n\nCongratulations! {company} has issued you an offer ({ctc}). Please accept or decline it in the portal.\n\nPlacement Cell"],
        ];
    }
}
