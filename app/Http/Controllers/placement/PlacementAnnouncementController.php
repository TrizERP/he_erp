<?php

namespace App\Http\Controllers\placement;

use App\Http\Requests\placement\PlacementCrudRequest;
use App\Models\placement\PlacementAnnouncement;
use App\Services\Placement\NotificationService;
use App\Services\Placement\StudentLookup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Section 3I: placement notice board. Optionally pushes the notice through the notification channels. */
class PlacementAnnouncementController extends PlacementCrudController
{
    protected function model(): string
    {
        return PlacementAnnouncement::class;
    }

    protected function title(): string
    {
        return 'Announcements & Notice Board';
    }

    protected function routePrefix(): string
    {
        return 'placement.announcements';
    }

    protected function columns(): array
    {
        return ['title' => 'Title', 'audience' => 'Audience', 'publish_at' => 'Publish', 'expires_at' => 'Expires', 'is_pinned' => 'Pinned', 'status' => 'Status'];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
            ['name' => 'audience', 'label' => 'Audience', 'type' => 'select', 'required' => true,
                'options' => ['all' => 'All students', 'registered' => 'Registered students', 'verified' => 'Verified students']],
            ['name' => 'publish_at', 'label' => 'Publish at', 'type' => 'datetime-local', 'help' => 'Empty = now'],
            ['name' => 'expires_at', 'label' => 'Expires at', 'type' => 'datetime-local'],
            ['name' => 'is_pinned', 'label' => 'Pinned', 'type' => 'select', 'options' => [0 => 'No', 1 => 'Yes'], 'required' => true],
            ['name' => 'notify', 'label' => 'Also send notification', 'type' => 'select', 'options' => [0 => 'No', 1 => 'Yes (in-app / email / WhatsApp templates)'], 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'required' => true],
            ['name' => 'standard_ids', 'label' => 'Courses', 'type' => 'multiselect', 'options' => $this->standardOptions(), 'help' => 'Empty = every course'],
            ['name' => 'body', 'label' => 'Notice', 'type' => 'textarea', 'required' => true],
        ];
    }

    protected function decorate($datatable)
    {
        return $datatable
            ->editColumn('publish_at', fn ($r) => $r->publish_at?->format('d-m-Y H:i') ?? 'Immediately')
            ->editColumn('expires_at', fn ($r) => $r->expires_at?->format('d-m-Y H:i') ?? '-')
            ->editColumn('is_pinned', fn ($r) => $r->is_pinned ? 'Yes' : 'No')
            ->editColumn('status', fn ($r) => $r->status ? 'Active' : 'Inactive');
    }

    public function rules(Request $request, ?int $id = null): array
    {
        return [
            'title'          => 'required|string|max:200',
            'body'           => 'required|string|max:5000',
            'audience'       => 'required|in:all,registered,verified',
            'publish_at'     => 'nullable|date',
            'expires_at'     => 'nullable|date|after:publish_at',
            'is_pinned'      => 'required|in:0,1',
            'notify'         => 'required|in:0,1',
            'status'         => 'required|in:0,1',
            'standard_ids'   => 'nullable|array',
            'standard_ids.*' => ['integer', Rule::exists('standard', 'id')->where('sub_institute_id', session('sub_institute_id'))],
        ];
    }

    public function store(PlacementCrudRequest $request)
    {
        $response = parent::store($request);

        if ($request->input('notify') == 1 && $response->getStatusCode() === 200) {
            $this->push($request, (int) $response->getData()->id);
        }

        return $response;
    }

    /** Sends the notice to its audience through the institute's "announcement" templates. */
    protected function push(Request $request, int $id): void
    {
        $students = app(StudentLookup::class)->students(null, $request->input('standard_ids') ?: null)->keys()->all();
        if ($request->input('audience') !== 'all') {
            $status = $request->input('audience') === 'verified' ? ['registered', 'verified'] : ['registered', null];
            $students = \App\Models\placement\PlacementStudentProfile::whereIn('student_id', $students)
                ->where('registration_status', 'registered')
                ->when($status[1] === 'verified', fn ($q) => $q->where('verification_status', 'verified'))
                ->pluck('student_id')->all();
        }

        app(NotificationService::class)->notify('announcement', $students, [
            'title' => $request->input('title'), 'notice' => $request->input('body'), 'date' => now()->format('d-m-Y'),
        ], 'announcement', $id);
    }
}
