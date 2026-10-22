@extends('pbas.layout')

@section('crumb', 'Update Profile')
@section('heading', 'GENERAL INFORMATION')

@section('alerts')
    <div class="alert alert-info alert-block">Info! Please write 00 in LWP if it is 0.</div>
    @if(session('pbas_updated'))
        <div class="alert alert-info alert-block">{{ session('pbas_updated') }}</div>
    @endif
@endsection

@section('content')
    @php
        $p = fn ($key) => old($key, $profile[$key] ?? '');
        $selectedDepartment = (int) old('department', $profile['department_id'] ?? 0);
    @endphp
    <div class="card">
        <h5 class="m-b-0">Personal-info</h5>
        <form action="{{ route('pbas.profile.store') }}" method="post" autocomplete="off">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Name of Faculty (in Block Letter)</label>
                    <input type="text" name="facultyname" class="form-control" value="{{ $p('facultyname') }}"/>
                </div>
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Father's Name / Mother's Name / Husband's Name</label>
                    <input type="text" name="fathername" class="form-control" value="{{ $p('fathername') }}"/>
                </div>
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Select Department</label>
                    {{-- ERP department master (hrms_departments) --}}
                    <select name="department" class="form-control">
                        <option value="">Select</option>
                        @foreach($departments as $id => $name)
                            <option value="{{ $id }}" @selected($selectedDepartment === (int) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Date of Joining (yyyy-mm-dd)</label>
                    <input type="text" name="dateofjoining" class="form-control pbas-datepicker" value="{{ $p('dateofjoining') }}">
                    <small class="text-muted">Strictly Date with Formate of (yyyy-mm-dd)</small>
                </div>
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Present Designation</label>
                    <input type="text" name="designation" class="form-control" value="{{ $p('designation') }}"/>
                </div>
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Pay Band &amp; AGP</label>
                    <input type="text" name="payband" class="form-control" value="{{ $p('payband') }}"/>
                </div>
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Date of last Promotion/Increment</label>
                    <input type="text" name="promotiondate" class="form-control" value="{{ $p('promotiondate') }}"/>
                    <small class="text-muted">Strictly Date with Formate of (yyyy-mm-dd)</small>
                </div>
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Permanent Address</label>
                    <input type="text" name="address" class="form-control" value="{{ $p('address') }}"/>
                    <small class="text-muted">Permanent Address with Pincode</small>
                </div>
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Your Email</label>
                    <input type="text" name="email" class="form-control" value="{{ $p('email') }}"/>
                </div>
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>Mobile No.</label>
                    <input type="text" name="mobile" class="form-control" value="{{ $p('mobile') }}"/>
                </div>
                <div class="col-md-8 form-group" style="margin-left:0;margin-right:0">
                    <label>Whether acquired any degree or fresh academic qualifications during the year</label>
                    <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $profile['extraqualification'] ?? '') }}" placeholder="Enter NA if Not Applicable"/>
                    <small class="text-muted">If yes Please Provide Data</small>
                </div>
                <div class="col-md-4 form-group" style="margin-left:0;margin-right:0">
                    <label>LWP during the year</label>
                    <input type="text" name="lwp" class="form-control" value="{{ $p('lwp') }}" placeholder="Enter -->00<-- if LWP is NIL"/>
                </div>
            </div>
            <button type="submit" name="register_user" value="Personal" class="btn btn-success">Save</button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            if ($.fn.datepicker) {
                $('.pbas-datepicker').datepicker({format: 'yyyy-mm-dd', autoclose: true});
            }
        });
    </script>
@endpush
