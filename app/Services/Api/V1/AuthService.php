<?php

namespace App\Services\Api\V1;

use Illuminate\Support\Facades\DB;

class AuthService
{
    /**
     * Same lookup as loginController: tbluser (stored password compared as the Blade login does) then
     * tblstudent (md5). Returns ['id' => int, 'kind' => 'user'|'student'] or null. Passwords are never returned.
     */
    public function attempt(string $email, string $password): ?array
    {
        $user = DB::table('tbluser')->where(['email' => $email, 'password' => $password, 'status' => '1'])->first(['id']);
        if ($user) {
            return ['id' => (int) $user->id, 'kind' => 'user'];
        }
        $student = DB::table('tblstudent')->where(['email' => $email, 'password' => md5($password), 'status' => '1'])->first(['id']);

        return $student ? ['id' => (int) $student->id, 'kind' => 'student'] : null;
    }
}
