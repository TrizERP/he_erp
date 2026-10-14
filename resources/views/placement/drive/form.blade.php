@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $d = $data['drive'];
    $isEdit = $d->exists;
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">{{ $isEdit ? 'Edit Drive' : 'New Drive' }}</h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ $isEdit ? route('placement.drives.show', $d->id) : route('placement.drives.index') }}" class="btn btn-default">Back</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger"><ul style="margin:0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="card">
            <form method="post" enctype="multipart/form-data"
                  action="{{ $isEdit ? route('placement.drives.update', $d->id) : route('placement.drives.store') }}">
                @csrf
                @if($isEdit) @method('PUT') @endif
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Drive title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="200" value="{{ old('title', $d->title) }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Company <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-control" required>
                            <option value="">-- Select --</option>
                            @foreach($data['companies'] as $id => $name)
                                <option value="{{ $id }}" @selected(old('company_id', $d->company_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Season</label>
                        <select name="season_id" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($data['seasons'] as $id => $name)
                                <option value="{{ $id }}" @selected(old('season_id', $d->season_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Mode <span class="text-danger">*</span></label>
                        <select name="drive_mode" class="form-control" required>
                            @foreach($data['modes'] as $key => $label)
                                <option value="{{ $key }}" @selected(old('drive_mode', $d->drive_mode) == $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Drive date</label>
                        <input type="date" name="drive_date" class="form-control" value="{{ old('drive_date', $d->drive_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Start time</label>
                        <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $d->start_time ? substr($d->start_time, 0, 5) : '') }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>End time</label>
                        <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $d->end_time ? substr($d->end_time, 0, 5) : '') }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Venue / meeting link</label>
                        <input type="text" name="venue" class="form-control" maxlength="255" value="{{ old('venue', $d->venue) }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Registration deadline</label>
                        <input type="datetime-local" name="registration_deadline" class="form-control"
                               value="{{ old('registration_deadline', $d->registration_deadline?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Job description file (pdf/doc, max 5 MB)</label>
                        <input type="file" name="jd_file" class="form-control" accept=".pdf,.doc,.docx">
                    </div>
                    <div class="col-md-12 form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $d->description) }}</textarea>
                    </div>
                    <div class="col-md-12 form-group">
                        <label>Bond / service agreement</label>
                        <textarea name="bond_details" class="form-control" rows="2">{{ old('bond_details', $d->bond_details) }}</textarea>
                    </div>
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('includes.footerJs')
    @include('includes.footer')
</div>
