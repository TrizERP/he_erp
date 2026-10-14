<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\placement\StudentPlacementController;
use App\Services\Placement\DashboardService;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Mobile-app endpoints for students (same JWT as apiController: payload carries student_id and
 * sub_institute_id). Each endpoint sets the session values the placement services read, then delegates
 * to the same code the web screens use, so web and app can never disagree on a rule.
 *
 * Responses follow the ERP convention: {"status":"1","message":"Success", ...data}
 * (status "0" = business / validation error, "2" = token failure).
 */
class PlacementApiController extends Controller
{
    use GetsJwtToken;

    protected function guard(Request $request): ?\Illuminate\Http\JsonResponse
    {
        try {
            if (! $this->jwtToken()->validate()) {
                return response()->json(['status' => '2', 'message' => 'Token Auth Failed', 'data' => []]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => '2', 'message' => $e->getMessage(), 'data' => []]);
        }

        $payload = (array) $this->jwtPayload();
        $studentId = $payload['student_id'] ?? null;
        $institute = $payload['sub_institute_id'] ?? null;
        $student = $studentId && $institute
            ? DB::table('tblstudent')->where(['id' => $studentId, 'sub_institute_id' => $institute])->first()
            : null;
        if (! $student) {
            return response()->json(['status' => '2', 'message' => 'Student not found', 'data' => []]);
        }

        // Everything below the controller reads these exactly as it would for a web login.
        session([
            'user_id'           => (int) $student->id,
            'sub_institute_id'  => (int) $institute,
            'user_profile_name' => 'Student',
            'syear'             => DB::table('school_setup')->where('Id', $institute)->value('syear'),
        ]);
        $request->merge(['type' => 'API']);

        return null;
    }

    /** Runs a student-controller action and returns its JSON (view-style actions). */
    protected function view(Request $request, string $method, array $args = [])
    {
        if ($denied = $this->guard($request)) {
            return $denied;
        }

        $json = app()->call([app(StudentPlacementController::class), $method], $args);

        return response($json, 200, ['Content-Type' => 'application/json']);
    }

    /** Runs an action that normally redirects with a flash message and turns it into JSON. */
    protected function act(Request $request, string $method, array $args = [])
    {
        if ($denied = $this->guard($request)) {
            return $denied;
        }

        try {
            app()->call([app(StudentPlacementController::class), $method], $args);
        } catch (ValidationException $e) {
            return response()->json(['status' => '0', 'message' => collect($e->errors())->flatten()->implode(' '), 'errors' => $e->errors()]);
        }

        $flash = session('data');

        return response()->json([
            'status'  => (string) ($flash['status_code'] ?? 1),
            'message' => $flash['message'] ?? 'Success',
        ]);
    }

    public function dashboard(Request $request)
    {
        if ($denied = $this->guard($request)) {
            return $denied;
        }

        return response()->json(['status' => '1', 'message' => 'Success', 'data' => app(DashboardService::class)->student((int) session('user_id'))]);
    }

    public function profile(Request $request)
    {
        return $this->view($request, 'profile');
    }

    public function updateProfile(Request $request)
    {
        return $this->act($request, 'updateProfile');
    }

    public function register(Request $request)
    {
        // the app shows the undertaking text and sends undertaking=1 when the student agrees
        return $this->act($request, 'register');
    }

    public function optOut(Request $request)
    {
        return $this->act($request, 'optOut');
    }

    public function drives(Request $request)
    {
        return $this->view($request, 'drives');
    }

    public function apply(Request $request)
    {
        $request->validate(['drive_id' => 'required|integer', 'role_id' => 'required|integer']);

        return $this->act($request, 'apply', ['drive' => (int) $request->drive_id]);
    }

    public function applications(Request $request)
    {
        return $this->view($request, 'applications');
    }

    public function withdraw(Request $request)
    {
        $request->validate(['application_id' => 'required|integer']);

        return $this->act($request, 'withdraw', ['application' => (int) $request->application_id]);
    }

    public function rounds(Request $request)
    {
        return $this->view($request, 'rounds');
    }

    public function offers(Request $request)
    {
        return $this->view($request, 'offers');
    }

    public function respondOffer(Request $request)
    {
        $request->validate(['offer_id' => 'required|integer', 'decision' => 'required|in:accepted,rejected']);

        return $this->act($request, 'respondOffer', ['offer' => (int) $request->offer_id]);
    }

    public function notices(Request $request)
    {
        return $this->view($request, 'notices');
    }

    public function training(Request $request)
    {
        return $this->view($request, 'training');
    }
}
