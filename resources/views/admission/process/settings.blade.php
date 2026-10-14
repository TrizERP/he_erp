@php
    $s = $data['settings'];
    $stdName = fn ($id) => $id ? ($data['standards']->firstWhere('id', $id)->name ?? ('#' . $id)) : 'All';
@endphp
@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    @include('admission.process._style')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12"><h4 class="page-title">Admission Settings</h4></div>
        </div>

        @include('admission.process._flash')

        <p class="adm-intro">These are the rules your institution decides. <b>Nothing is switched on until you choose it</b>, so the module behaves like before until then. Changes are saved in the history.</p>

        {{-- Policy switches ---------------------------------------------------------------------------------- --}}
        <div class="card">
            <h4>Confirmation policy</h4>
            <p class="text-muted">Nothing here is pre-approved: each rule stays off until your institution decides to enforce it. Enforced rules can only be bypassed at confirmation by an administrator, with a recorded reason.</p>
            <form method="POST" action="{{ route('admission.process.settings_save') }}" onsubmit="this.querySelector('button[type=submit]').disabled = true;">@csrf
                @foreach ([
                    'require_documents_verified' => 'Require all mandatory documents to be verified',
                    'require_eligibility' => 'Require eligibility status "Eligible" (an Ineligible applicant is always blocked)',
                    'require_selection' => 'Require selection status "Selected"',
                    'require_offer_accepted' => 'Require an accepted offer',
                    'allow_fee_waiver' => 'Allow administrators to waive the admission fee',
                ] as $key => $label)
                    <div class="checkbox"><label><input type="checkbox" name="{{ $key }}" value="1" @checked(($s[$key] ?? '0') === '1')> {{ $label }}</label></div>
                @endforeach
                <div class="row" style="margin-top:10px">
                    <div class="col-md-4 form-group"><label>Admission fee condition for confirmation</label>
                        <select name="fee_condition" class="form-control">
                            @foreach (['none' => 'Not required', 'any_payment' => 'Some payment recorded', 'full' => 'Fully paid'] as $value => $label)
                                <option value="{{ $value }}" @selected(($s['fee_condition'] ?? 'none') === $value)>{{ $label }}</option>
                            @endforeach
                        </select></div>
                    <div class="col-md-4 form-group"><label>Duplicate inquiry check</label>
                        <select name="duplicate_inquiry_match" class="form-control">
                            @foreach (['none' => 'Off', 'mobile' => 'Same mobile', 'email' => 'Same email', 'mobile_name' => 'Same mobile and first name'] as $value => $label)
                                <option value="{{ $value }}" @selected(($s['duplicate_inquiry_match'] ?? 'none') === $value)>{{ $label }}</option>
                            @endforeach
                        </select></div>
                </div>
                <button type="submit" class="btn btn-success">Save settings</button>
            </form>
        </div>

        {{-- Required documents -------------------------------------------------------------------------------- --}}
        <div class="card">
            <h4>Required documents</h4>
            <p class="text-muted">Leave programme / year / category blank to apply to everyone. Removing a document type deactivates it; files already uploaded are kept.</p>
            <div class="table-responsive"><table class="table table-condensed">
                <thead><tr><th>Name</th><th>Programme</th><th>Year</th><th>Category</th><th>Mandatory</th><th>Types</th><th>Max KB</th><th>Active</th><th></th></tr></thead>
                <tbody>
                @forelse ($data['requirements'] as $r)
                    <tr class="{{ $r->status ? '' : 'text-muted' }}">
                        <td>{{ $r->name }}</td><td>{{ $stdName($r->standard_id) }}</td><td>{{ $r->syear ?? 'All' }}</td><td>{{ $r->category ?? 'All' }}</td>
                        <td>{{ $r->is_mandatory ? 'Yes' : 'No' }}</td><td>{{ $r->allowed_mimes ?? 'default' }}</td><td>{{ $r->max_kb ?? 'default' }}</td><td>{{ $r->status ? 'Yes' : 'No' }}</td>
                        <td>@if ($r->status)
                            <form method="POST" action="{{ route('admission.process.requirement_remove', $r->id) }}" style="display:inline" onsubmit="return confirm('Deactivate this document type?');">@csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger">Remove</button></form>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-muted">No required documents configured.</td></tr>
                @endforelse
                </tbody></table></div>
            <form method="POST" action="{{ route('admission.process.requirement_add') }}" class="form-inline" onsubmit="this.querySelector('button[type=submit]').disabled = true;">@csrf
                <input type="text" name="name" class="form-control" placeholder="Document name" required maxlength="120">
                <select name="standard_id" class="form-control"><option value="">All programmes</option>@foreach ($data['standards'] as $st)<option value="{{ $st->id }}">{{ $st->name }}</option>@endforeach</select>
                <input type="number" name="syear" class="form-control" placeholder="Year (blank = all)" style="width:140px">
                <input type="text" name="category" class="form-control" placeholder="Category (blank = all)" maxlength="50">
                <input type="text" name="allowed_mimes" class="form-control" placeholder="pdf,jpg,png" maxlength="100" style="width:120px">
                <input type="number" name="max_kb" class="form-control" placeholder="Max KB" min="10" max="10240" style="width:100px">
                <label class="checkbox-inline"><input type="checkbox" name="is_mandatory" value="1" checked> Mandatory</label>
                <button type="submit" class="btn btn-success">Add</button>
            </form>
        </div>

        {{-- Eligibility rules ----------------------------------------------------------------------------------- --}}
        <div class="card">
            <h4>Eligibility criteria</h4>
            <p class="text-muted">Enter only criteria your institution has formally approved. With none configured every applicant is routed to "Needs review"; a missing or unreadable value is never an automatic rejection.</p>
            <div class="table-responsive"><table class="table table-condensed">
                <thead><tr><th>Label</th><th>Programme</th><th>Year</th><th>Rule</th><th>Mandatory</th><th>Active</th><th></th></tr></thead>
                <tbody>
                @forelse ($data['rules'] as $rule)
                    <tr class="{{ $rule->status ? '' : 'text-muted' }}">
                        <td>{{ $rule->label }}</td><td>{{ $stdName($rule->standard_id) }}</td><td>{{ $rule->syear ?? 'All' }}</td>
                        <td>{{ $data['fields'][$rule->field] ?? $rule->field }} {{ $rule->operator }} {{ $rule->value }}</td>
                        <td>{{ $rule->is_mandatory ? 'Yes' : 'No' }}</td><td>{{ $rule->status ? 'Yes' : 'No' }}</td>
                        <td>@if ($rule->status)
                            <form method="POST" action="{{ route('admission.process.rule_remove', $rule->id) }}" style="display:inline" onsubmit="return confirm('Deactivate this criterion?');">@csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger">Remove</button></form>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No criteria configured.</td></tr>
                @endforelse
                </tbody></table></div>
            <form method="POST" action="{{ route('admission.process.rule_add') }}" class="form-inline" onsubmit="this.querySelector('button[type=submit]').disabled = true;">@csrf
                <input type="text" name="label" class="form-control" placeholder="Label" required maxlength="120">
                <select name="field" class="form-control" required>@foreach ($data['fields'] as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>
                <select name="operator" class="form-control" required>@foreach ($data['operators'] as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>
                <input type="text" name="value" class="form-control" placeholder="Value" maxlength="255">
                <select name="standard_id" class="form-control"><option value="">All programmes</option>@foreach ($data['standards'] as $st)<option value="{{ $st->id }}">{{ $st->name }}</option>@endforeach</select>
                <input type="number" name="syear" class="form-control" placeholder="Year (blank = all)" style="width:140px">
                <label class="checkbox-inline"><input type="checkbox" name="is_mandatory" value="1" checked> Mandatory</label>
                <button type="submit" class="btn btn-success">Add</button>
            </form>
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
