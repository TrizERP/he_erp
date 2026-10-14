<?php

namespace App\Http\Controllers\placement;

use App\Models\placement\PlacementTemplate;
use Illuminate\Http\Request;

class PlacementTemplateController extends PlacementCrudController
{
    protected function model(): string
    {
        return PlacementTemplate::class;
    }

    protected function title(): string
    {
        return 'Email / WhatsApp / In-app Templates';
    }

    protected function routePrefix(): string
    {
        return 'placement.templates';
    }

    protected function columns(): array
    {
        return ['channel' => 'Channel', 'event_key' => 'Event', 'name' => 'Name', 'status' => 'Status'];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'channel', 'label' => 'Channel', 'type' => 'select', 'options' => PlacementTemplate::CHANNELS, 'required' => true],
            ['name' => 'event_key', 'label' => 'Event key', 'type' => 'text', 'required' => true, 'help' => 'drive_published, round_result, offer_issued ...'],
            ['name' => 'name', 'label' => 'Template name', 'type' => 'text', 'required' => true],
            ['name' => 'subject', 'label' => 'Subject (email)', 'type' => 'text'],
            ['name' => 'body', 'label' => 'Body', 'type' => 'textarea', 'required' => true, 'help' => 'Placeholders: '.implode(' ', PlacementTemplate::PLACEHOLDERS)],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [1 => 'Active', 0 => 'Inactive'], 'required' => true],
        ];
    }

    protected function decorate($datatable)
    {
        return $datatable->editColumn('status', fn ($r) => $r->status ? 'Active' : 'Inactive');
    }

    public function rules(Request $request, ?int $id = null): array
    {
        return [
            'channel'   => 'required|in:email,whatsapp,inapp',
            'event_key' => 'required|string|max:50|alpha_dash',
            'name'      => 'required|string|max:150',
            'subject'   => 'nullable|string|max:255|required_if:channel,email',
            'body'      => 'required|string|max:5000',
            'status'    => 'required|in:0,1',
        ];
    }
}
