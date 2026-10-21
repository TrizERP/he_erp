<?php

namespace App\Http\Controllers\api\V1\SchoolSetup;

use App\Http\Controllers\api\V1\BaseApiController;
use App\Http\Controllers\api\V1\Concerns\PaginatesLists;
use App\Http\Requests\V1\SchoolSetup\DivisionRequest;
use App\Http\Resources\V1\SchoolSetup\DivisionResource;
use App\Models\school_setup\divisionModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DivisionController extends BaseApiController
{
    use PaginatesLists;

    public function index(Request $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');

        return $this->listPage(
            $request,
            divisionModel::query()->where('sub_institute_id', $c['institute_id']),
            ['name' => 'name', 'id' => 'id'],
            ['name'],
            'name',
            fn ($m) => (new DivisionResource($m))->resolve(),
        );
    }

    public function store(DivisionRequest $request): JsonResponse
    {
        $c = $request->attributes->get('api_context');
        $name = $request->validated()['name'];

        if (divisionModel::where(['name' => $name, 'sub_institute_id' => $c['institute_id']])->exists()) {
            throw ValidationException::withMessages(['name' => ['Division Already Exists']]);
        }

        $row = divisionModel::create(['name' => $name, 'sub_institute_id' => $c['institute_id']]);

        return $this->ok((new DivisionResource($row))->resolve(), 'Division Added Successfully', null, 201);
    }
}
