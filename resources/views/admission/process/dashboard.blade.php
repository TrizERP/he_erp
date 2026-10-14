@php
    $stages = app(\App\Services\Admission\StageService::class);
    $canSettings = \App\Services\Admission\AdmissionAccess::can('settings');
    // key => [icon, colour, plain-language explanation]
    $action = [
        'pending_documents'   => ['fa-file-text-o', '#fb8c00', 'Documents to verify', 'Applicants whose uploaded documents are waiting for your check'],
        'pending_eligibility' => ['fa-check-square-o', '#8e24aa', 'Eligibility to check', 'Submitted applications not yet checked against the criteria'],
        'offers_awaiting'     => ['fa-envelope-o', '#1e88e5', 'Offers waiting for a reply', 'Offers sent; the applicant has not answered yet'],
        'pending_fees'        => ['fa-money', '#e53935', 'Admission fee not received', 'Registered applicants with no payment recorded'],
    ];
    $overview = [
        'total_inquiries'        => ['fa-users', '#00897b', 'All inquiries', 'Everyone who has enquired this year'],
        'draft_applications'     => ['fa-pencil', '#78909c', 'Drafts', 'Application started but not submitted yet'],
        'submitted_applications' => ['fa-paper-plane', '#1e88e5', 'Submitted applications', 'Complete applications under process'],
        'confirmed'              => ['fa-graduation-cap', '#43a047', 'Admitted students', 'Admission confirmed, student record created'],
    ];
@endphp
@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    @include('admission.process._style')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12">
                <h4 class="page-title">Admission Dashboard</h4>
            </div>
        </div>

        @include('admission.process._flash')

        <p class="adm-intro">Follow every applicant from first inquiry to confirmed admission. The coloured boxes show what needs attention - <strong>click a box to see exactly those applicants.</strong></p>
        <p class="adm-scope">Showing: institute #{{ session('sub_institute_id') }}, academic year {{ session('syear') }}.</p>

        <div class="adm-actions">
            <a href="{{ route('admission.review.index') }}" class="btn btn-primary"><i class="fa fa-list"></i> All applications</a>
            <a href="{{ route('admission_enquiry.create') }}" class="btn btn-success"><i class="fa fa-plus"></i> New inquiry</a>
            <a href="{{ route('admission.reports.index') }}" class="btn btn-default"><i class="fa fa-bar-chart"></i> Reports</a>
            @if ($canSettings)<a href="{{ route('admission.settings.index') }}" class="btn btn-default"><i class="fa fa-cog"></i> Settings</a>@endif
        </div>

        <details class="adm-how">
            <summary>How admission works (7 simple steps)</summary>
            <ol>
                <li><b>Inquiry</b> - a family asks about a programme. Save their details.</li>
                <li><b>Application</b> - turn the inquiry into an application form and submit it when complete.</li>
                <li><b>Documents</b> - upload the required certificates; a person verifies each one.</li>
                <li><b>Eligibility</b> - check the applicant against your approved criteria.</li>
                <li><b>Selection</b> - shortlist, waitlist, select or reject.</li>
                <li><b>Offer and fee</b> - send an offer, get the reply, receive the admission fee.</li>
                <li><b>Confirm</b> - confirm the admission. The student record is created automatically, once.</li>
            </ol>
        </details>

        <div class="adm-h">Needs your action <small>- these are waiting for someone</small></div>
        <div class="row">
            @foreach ($action as $key => [$icon, $color, $title, $sub])
                @php $count = $data['metrics'][$key]['count']; @endphp
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('admission.dashboard.drill', $key) }}" class="adm-tile {{ $count ? '' : 'clear' }}" style="--c:{{ $color }}">
                        <i class="fa {{ $icon }} ico"></i>
                        <div class="num">{{ $count }}</div>
                        <div class="ttl">{{ $title }}</div>
                        <div class="sub">{!! $count ? e($sub) : '<span class="adm-clear-tag"><i class="fa fa-check"></i> All clear</span>' !!}</div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="adm-h" style="margin-top:6px">Overview</div>
        <div class="row">
            @foreach ($overview as $key => [$icon, $color, $title, $sub])
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('admission.dashboard.drill', $key) }}" class="adm-tile" style="--c:{{ $color }}">
                        <i class="fa {{ $icon }} ico"></i>
                        <div class="num">{{ $data['metrics'][$key]['count'] }}</div>
                        <div class="ttl">{{ $title }}</div>
                        <div class="sub">{{ $sub }}</div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="card">
            <div class="adm-h">Latest applicants</div>
            <div class="table-responsive">
                <table class="table table-hover adm-table">
                    <thead><tr><th>Applicant</th><th>Programme</th><th>Where it is now</th><th>What to do next</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($data['recent'] as $row)
                        @php $stage = $stages->describe($row); @endphp
                        <tr>
                            <td><span class="adm-name">{{ trim($row->first_name . ' ' . $row->last_name) }}</span><br><span class="adm-sub">Enquiry {{ $row->enquiry_no }}</span></td>
                            <td>{{ $row->standard_name ?? '-' }}</td>
                            <td><span class="label label-{{ $stage['color'] }}">{{ $stage['label'] }}</span></td>
                            <td class="adm-sub" style="font-size:13px">{{ $stage['next'] }}</td>
                            <td class="text-right"><a href="{{ route('admission.review.show', $row->id) }}" class="btn btn-sm btn-primary">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">
                            <div class="adm-empty"><i class="fa fa-inbox big"></i><b>No inquiries yet for this academic year</b>
                                Start with <a href="{{ route('admission_enquiry.create') }}">New inquiry</a>, or check that the year in the header is the one you expect.</div>
                        </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
