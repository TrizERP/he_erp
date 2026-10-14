<?php

namespace App\Http\Controllers\admission;

use App\Http\Controllers\Controller;
use App\Services\Admission\AdmissionException;
use Closure;
use Illuminate\Http\RedirectResponse;

/**
 * Shared plumbing for the admission workflow screens. The institute always comes from the session
 * (never from the request), and every service call is scoped to it.
 */
abstract class AdmissionController extends Controller
{
    protected function instituteId(): int
    {
        return (int) session()->get('sub_institute_id');
    }

    protected function syear(): ?int
    {
        $syear = session()->get('syear');

        return $syear ? (int) $syear : null;
    }

    /** Run a service call; business-rule violations come back as a flash message, not a 500. */
    protected function attempt(Closure $action, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (AdmissionException $e) {
            return $this->flash(0, $e->getMessage(), $e->details);
        }

        return $this->flash(1, $success);
    }

    protected function flash(int $status, string $message, array $details = []): RedirectResponse
    {
        return redirect()->back()->with(['data' => ['status_code' => $status, 'message' => $message, 'details' => $details]]);
    }
}
