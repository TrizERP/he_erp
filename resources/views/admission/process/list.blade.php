@php
    $stages = app(\App\Services\Admission\StageService::class);
    $friendly = [
        'draft' => 'Draft (not submitted)', 'submitted' => 'Submitted', 'reopened' => 'Reopened for correction',
        'pending_review' => 'Not checked yet', 'eligible' => 'Eligible', 'ineligible' => 'Not eligible', 'needs_review' => 'Needs a person to decide',
        'under_review' => 'Under review', 'shortlisted' => 'Shortlisted', 'waitlisted' => 'Waitlisted', 'rejected' => 'Rejected', 'selected' => 'Selected',
        'pending' => 'Not confirmed yet', 'confirmed' => 'Admitted', 'on_hold' => 'On hold', 'cancelled' => 'Cancelled',
    ];
    $filterSets = [
        'application_status'  => ['Application', ['draft', 'submitted', 'reopened']],
        'eligibility_status'  => ['Eligibility', array_keys(\App\Models\admission\AdmissionWorkflow::ELIGIBILITY)],
        'selection_status'    => ['Selection', array_keys(\App\Models\admission\AdmissionWorkflow::SELECTION)],
        'confirmation_status' => ['Admission', array_keys(\App\Models\admission\AdmissionWorkflow::CONFIRMATION)],
    ];
    $hints = [
        'Merit / Selection' => 'Submitted applications ready for a selection decision. Open one to shortlist, select or reject.',
        'Admission Confirmation' => 'Submitted applications. Open one to see its confirmation checklist and confirm the admission.',
        'Admitted Students' => 'Applicants whose admission is confirmed and who now have a student record.',
        'Application Review' => 'Every applicant for this academic year. Open one to see its documents, eligibility, offer, fee and history in one place.',
    ];
@endphp
@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    @include('admission.process._style')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">{{ $data['title'] }}</h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('admission.dashboard.index') }}" class="btn btn-default"><i class="fa fa-th-large"></i> Dashboard</a>
            </div>
        </div>

        @include('admission.process._flash')

        <p class="adm-intro">{{ $hints[$data['title']] ?? 'The applicants behind the number you clicked. Open one to see everything about it.' }}</p>

        <div class="card">
            <form method="GET" class="row">
                <div class="col-md-4 form-group">
                    <label>Find an applicant</label>
                    <input type="text" name="q" value="{{ $data['filters']['q'] ?? '' }}" class="form-control" placeholder="Name, mobile number or enquiry number">
                </div>
                @if (empty($data['metric']))
                    @foreach ($filterSets as $field => [$label, $options])
                        @continue(isset($data['preset'][$field]))
                        <div class="col-md-2 col-sm-6 form-group">
                            <label>{{ $label }}</label>
                            <select name="{{ $field }}" class="form-control">
                                <option value="">Any</option>
                                @foreach ($options as $option)
                                    <option value="{{ $option }}" @selected(($data['filters'][$field] ?? '') === $option)>{{ $friendly[$option] ?? ucwords(str_replace('_', ' ', $option)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                @endif
                <div class="col-md-2 form-group"><label>&nbsp;</label><br>
                    <button class="btn btn-success"><i class="fa fa-search"></i> Search</button>
                    @if (! empty($data['filters']['q']) || count(array_diff_key(array_filter($data['filters'] ?? []), $data['preset'] ?? [], ['q' => 1])))
                        <a href="{{ url()->current() }}" class="btn btn-link">Clear</a>
                    @endif
                </div>
            </form>

            <p class="adm-sub">{{ $data['rows']->total() }} applicant(s) found</p>
            <div class="table-responsive">
                <table class="table table-hover adm-table">
                    <thead>
                    <tr><th>Applicant</th><th>Programme</th><th>Where it is now</th><th>What to do next</th><th></th></tr>
                    </thead>
                    <tbody>
                    @forelse ($data['rows'] as $row)
                        @php $stage = $stages->describe($row); @endphp
                        <tr>
                            <td>
                                <span class="adm-name">{{ trim($row->first_name . ' ' . $row->middle_name . ' ' . $row->last_name) }}</span><br>
                                <span class="adm-sub">Enquiry {{ $row->enquiry_no }}@if ($row->form_no) &middot; Form {{ $row->form_no }}@endif &middot; {{ $row->mobile }}</span>
                            </td>
                            <td>{{ $row->standard_name ?? '-' }}</td>
                            <td>
                                <span class="label label-{{ $stage['color'] }}">{{ $stage['label'] }}</span>
                                @if ($row->student_id)<br><span class="adm-sub">Student #{{ $row->student_id }}</span>@endif
                            </td>
                            <td class="adm-sub" style="font-size:13px;max-width:320px">{{ $stage['next'] }}</td>
                            <td class="text-right" style="white-space:nowrap"><a href="{{ route('admission.review.show', $row->id) }}" class="btn btn-sm btn-primary">Open <i class="fa fa-angle-right"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">
                            <div class="adm-empty"><i class="fa fa-search big"></i><b>No applicants match</b>
                                Try a shorter search, or clear the filters. Check that the academic year in the header is the right one.</div>
                        </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data['rows']->links() }}
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
