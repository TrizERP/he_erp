@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php $t = $data['training']; $isEdit = $t->exists; @endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12"><h4 class="page-title">{{ $isEdit ? 'Edit session' : 'New session' }}</h4></div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right"><a href="{{ route('placement.training.index') }}" class="btn btn-default">Back</a></div>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger"><ul style="margin:0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <div class="card">
            <form method="post" action="{{ $isEdit ? route('placement.training.update', $t->id) : route('placement.training.store') }}">
                @csrf @if($isEdit) @method('PUT') @endif
                <div class="row">
                    <div class="col-md-6 form-group"><label>Title *</label><input name="title" class="form-control" required maxlength="200" value="{{ old('title', $t->title) }}"></div>
                    <div class="col-md-3 form-group"><label>Type *</label>
                        <select name="training_type" class="form-control" required>@foreach($data['types'] as $k => $l)<option value="{{ $k }}" @selected(old('training_type', $t->training_type) == $k)>{{ $l }}</option>@endforeach</select></div>
                    <div class="col-md-3 form-group"><label>Status *</label>
                        <select name="status" class="form-control">@foreach(['scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $l)<option value="{{ $k }}" @selected(old('status', $t->status) == $k)>{{ $l }}</option>@endforeach</select></div>
                    <div class="col-md-3 form-group"><label>Date *</label><input type="date" name="session_date" class="form-control" required value="{{ old('session_date', $t->session_date?->format('Y-m-d')) }}"></div>
                    <div class="col-md-2 form-group"><label>Start</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time', $t->start_time ? substr($t->start_time, 0, 5) : '') }}"></div>
                    <div class="col-md-2 form-group"><label>End</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time', $t->end_time ? substr($t->end_time, 0, 5) : '') }}"></div>
                    <div class="col-md-5 form-group"><label>Trainer</label><input name="trainer" class="form-control" value="{{ old('trainer', $t->trainer) }}"></div>
                    <div class="col-md-6 form-group"><label>Venue</label><input name="venue" class="form-control" value="{{ old('venue', $t->venue) }}"></div>
                    <div class="col-md-3 form-group"><label>Maximum score (tests)</label><input type="number" step="0.01" name="max_score" class="form-control" value="{{ old('max_score', $t->max_score) }}"></div>
                    <div class="col-md-12 form-group"><label>Batch: courses attending <small class="text-muted">(registered students of these courses are assigned automatically; none = all)</small></label>
                        <select name="standard_ids[]" class="form-control" multiple size="5">@foreach($data['standards'] as $id => $n)<option value="{{ $id }}" @selected(in_array($id, old('standard_ids', $t->standard_ids ?? [])))>{{ $n }}</option>@endforeach</select></div>
                    <div class="col-md-12 form-group"><label>Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $t->description) }}</textarea></div>
                    <div class="col-md-12 text-center"><button class="btn btn-success">Save</button></div>
                </div>
            </form>
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
