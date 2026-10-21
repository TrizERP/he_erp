<?php

namespace App\Http\Controllers\api\V1\Meta;

use App\Http\Controllers\api\V1\BaseApiController;
use App\Services\Api\V1\LabelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabelController extends BaseApiController
{
    /** GET /labels: wording for the current institute (e.g. standard => "Semester"). */
    public function index(Request $request, LabelService $labels): JsonResponse
    {
        return $this->ok($labels->map((int) $request->attributes->get('api_context')['institute_id']));
    }
}
