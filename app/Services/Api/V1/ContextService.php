<?php

namespace App\Services\Api\V1;

use Illuminate\Support\Facades\DB;

/**
 * Resolves the per-request context (institute / academic year / term) that the Blade app keeps in the session,
 * and mirrors it into the (array-backed) session so reused helpers and models that call session() keep working.
 */
class ContextService
{
    /** @return array|null principal (user or student row as array + kind), null if it no longer exists/active */
    public function principal(int $id, string $kind): ?array
    {
        $table = $kind === 'student' ? 'tblstudent' : 'tbluser';
        $row = DB::table($table)->where('id', $id)->where('status', '1')->first();
        if (! $row) {
            return null;
        }
        $row = (array) $row;
        $row['kind'] = $kind;
        if ($kind === 'student') {
            $row['user_name'] = $row['username'] ?? '';
            $row['is_admin'] = 0;
            $row['client_id'] = null;
        }

        return $row;
    }

    public function isClientAdmin(array $p): bool
    {
        return $p['kind'] === 'user' && (int) $p['sub_institute_id'] === 0 && (int) $p['is_admin'] === 1 && ! empty($p['client_id']);
    }

    /** Institutes the principal may work in. */
    public function institutes(array $p): array
    {
        if ($this->isClientAdmin($p)) {
            $q = DB::table('school_setup')->where('client_id', $p['client_id']);
        } else {
            $q = DB::table('school_setup')->whereIn('id', array_filter(explode(',', (string) $p['sub_institute_id'])));
        }

        return $q->get(['id', 'SchoolName', 'ShortCode'])->map(fn ($r) => (array) $r)->all();
    }

    /**
     * @param  array  $requested  ['institute_id' => ?, 'syear' => ?, 'term_id' => ?]
     * @return array|null context, or null if the requested institute is not allowed
     */
    public function resolve(array $p, array $requested = []): ?array
    {
        $allowed = array_map('intval', array_column($this->institutes($p), 'id'));
        $institute = (int) ($requested['institute_id'] ?? 0);
        if ($institute && ! in_array($institute, $allowed, true)) {
            return null;
        }
        if (! $institute) {
            $institute = $allowed[0] ?? 0;
        }

        $school = DB::table('school_setup')->where('id', $institute)->first();
        $years = DB::table('academic_year')->where('sub_institute_id', $institute)->orderBy('sort_order')->get();

        $syear = $requested['syear'] ?? null;
        $term = $requested['term_id'] ?? null;
        $known = $syear && $years->contains(fn ($r) => (string) $r->syear === (string) $syear);
        if (! $known) {
            $today = date('Y-m-d');
            $current = $years->first(fn ($r) => $r->start_date <= $today && $today <= $r->end_date);
            $syear = $current->syear ?? null;
            $term = $term ?: ($current->term_id ?? null);
        } elseif (! $term) {
            $term = $years->first(fn ($r) => (string) $r->syear === (string) $syear)->term_id ?? null;
        }

        $profile = DB::table('tbluserprofilemaster')->where('id', $p['user_profile_id'])->first();

        return [
            'institute_id' => $institute,
            'institute_name' => $school->SchoolName ?? null,
            'institute_type' => $school->institute_type ?? null,
            'client_id' => $p['client_id'] ?? ($school->client_id ?? null),
            'syear' => $syear,
            'term_id' => $term,
            'user_id' => (int) $p['id'],
            'kind' => $p['kind'],
            'user_profile_id' => (int) $p['user_profile_id'],
            'user_profile_name' => $profile->name ?? null,
            'is_admin' => (int) $p['is_admin'],
            'is_client_admin' => $this->isClientAdmin($p),
            'name' => trim(($p['first_name'] ?? '').' '.($p['last_name'] ?? '')),
            'email' => $p['email'] ?? null,
        ];
    }

    /** Mirror the context into the session keys the legacy code reads. */
    public function applyToSession(array $c, array $p): void
    {
        session()->flush();
        session()->put([
            'user_id' => $c['user_id'],
            'user_profile_id' => $c['user_profile_id'],
            'user_profile_name' => $c['user_profile_name'],
            'sub_institute_id' => $c['institute_id'],
            'client_id' => $c['client_id'],
            'is_admin' => $c['is_admin'],
            'syear' => $c['syear'],
            'term_id' => $c['term_id'],
            'institute_type' => $c['institute_type'],
            'user_name' => $p['user_name'] ?? null,
            'name' => $c['name'],
            'email' => $c['email'],
        ]);
    }
}
