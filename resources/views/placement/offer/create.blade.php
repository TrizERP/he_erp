@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php $ppo = $data['ppo']; @endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">{{ $ppo ? 'PPO Conversion' : 'Record Offer' }}</h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('placement.offers.index') }}" class="btn btn-default">Back</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin:0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                @if(! $errors->has('override_reason'))<small>A policy block can be overridden with a written reason below; the override is audited.</small>@endif
            </div>
        @endif

        <div class="card">
            <form method="post" enctype="multipart/form-data" action="{{ route('placement.offers.store') }}">
                @csrf
                <div class="row">
                    @if($ppo)
                        <input type="hidden" name="is_ppo" value="1">
                        <div class="col-md-4 form-group"><label>Student enrollment no. *</label><input name="enrollment_no" class="form-control" required value="{{ old('enrollment_no') }}"></div>
                        <div class="col-md-4 form-group"><label>Company *</label>
                            <select name="company_id" class="form-control" required>
                                <option value="">-- Select --</option>@foreach($data['companies'] as $id => $n)<option value="{{ $id }}" @selected(old('company_id') == $id)>{{ $n }}</option>@endforeach
                            </select></div>
                        <div class="col-md-4 form-group"><label>Internship (optional)</label>
                            <select name="internship_id" class="form-control">
                                <option value="">-- None --</option>@foreach($data['internships'] as $i)<option value="{{ $i->internship_id }}">{{ $i->title }} (student {{ $i->student_id }})</option>@endforeach
                            </select></div>
                    @else
                        <div class="col-md-8 form-group"><label>Applicant *</label>
                            <select name="application_id" id="application" class="form-control" required>
                                <option value="">-- Select shortlisted / selected applicant --</option>
                                @foreach($data['applications'] as $a)<option value="{{ $a->id }}" data-role="{{ $a->role_title }}" @selected(old('application_id') == $a->id)>{{ $a->label }}</option>@endforeach
                            </select></div>
                    @endif
                    <div class="col-md-4 form-group"><label>Role title *</label><input name="role_title" id="role_title" class="form-control" required value="{{ old('role_title') }}"></div>
                    <div class="col-md-3 form-group"><label>Fixed CTC (LPA)</label><input type="number" step="0.01" min="0" name="ctc_fixed" class="form-control" value="{{ old('ctc_fixed') }}"></div>
                    <div class="col-md-3 form-group"><label>Variable (LPA)</label><input type="number" step="0.01" min="0" name="ctc_variable" class="form-control" value="{{ old('ctc_variable') }}"></div>
                    <div class="col-md-3 form-group"><label>Joining bonus (LPA)</label><input type="number" step="0.01" min="0" name="joining_bonus" class="form-control" value="{{ old('joining_bonus') }}"></div>
                    <div class="col-md-3 form-group"><label>Location</label><input name="location" class="form-control" value="{{ old('location') }}"></div>
                    <div class="col-md-3 form-group"><label>Offer date</label><input type="date" name="offer_date" class="form-control" value="{{ old('offer_date', now()->format('Y-m-d')) }}"></div>
                    <div class="col-md-3 form-group"><label>Joining date</label><input type="date" name="joining_date" class="form-control" value="{{ old('joining_date') }}"></div>
                    <div class="col-md-6 form-group"><label>Offer letter (PDF, max 5 MB)</label><input type="file" name="offer_letter" accept=".pdf" class="form-control"></div>
                    <div class="col-md-12 form-group"><label>Bond / service agreement</label><textarea name="bond_details" class="form-control" rows="2">{{ old('bond_details') }}</textarea></div>
                    <div class="col-md-12 form-group"><label>Policy override reason <small class="text-muted">(only if the policy blocks this offer; min 10 characters)</small></label>
                        <input name="override_reason" class="form-control" value="{{ old('override_reason') }}"></div>
                    <div class="col-md-12 text-center"><button class="btn btn-success">Record offer</button></div>
                </div>
            </form>
        </div>
    </div>
    @include('includes.footerJs')
    <script>
        $(function () {
            $('#application').on('change', function () { var r = $(this).find(':selected').data('role'); if (r && !$('#role_title').val()) { $('#role_title').val(r); } });
        });
    </script>
    @include('includes.footer')
</div>
