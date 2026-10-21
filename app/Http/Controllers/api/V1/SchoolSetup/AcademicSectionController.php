<?php

namespace App\Http\Controllers\api\V1\SchoolSetup;

use App\Http\Controllers\api\V1\BaseApiController;
use App\Http\Controllers\api\V1\Concerns\PaginatesLists;
use App\Http\Requests\V1\SchoolSetup\AcademicSectionRequest;
use App\Http\Resources\V1\SchoolSetup\AcademicSectionResource;
use App\Models\school_setup\academic_sectionModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** "Academy" master of the Blade Add Masters screen (academic_section table). */
class AcademicSectionController extends BaseApiController
{
    use PaginatesLists;

    public function index(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');

        return $this->listPage(
            $request,
            academic_sectionModel::query()->where('sub_institute_id', $c['institute_id']),
            ['title' => 'title', 'short_name' => 'short_name', 'sort_order' => 'sort_order', 'shift' => 'shift', 'medium' => 'medium'],
            ['title', 'short_name', 'shift', 'medium'],
            'sort_order',
            fn ($m) => (new AcademicSectionResource($m))->resolve(),
        );
    }

    public function store(AcademicSectionRequest $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $data = $request->validated();

        if (academic_sectionModel::where(['title' => $data['title'], 'sub_institute_id' => $c['institute_id']])->exists()) {
            throw ValidationException::withMessages(['title' => ['Academic Section Already Exists !']]);
        }

        $row = academic_sectionModel::create($data + ['sub_institute_id' => $c['institute_id']]);

        return $this->ok((new AcademicSectionResource($row))->resolve(), 'Academic Section Added Successfully !', null, 201);
    }
}
