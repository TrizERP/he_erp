@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
@php
    $c = $data['company'];
    $isEdit = $c->exists;
@endphp
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <h4 class="page-title">{{ $isEdit ? 'Edit Company' : 'Add Company' }}</h4>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 text-right">
                <a href="{{ route('placement.companies.index') }}" class="btn btn-default">Back</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin:0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card">
            <form method="post" enctype="multipart/form-data"
                  action="{{ $isEdit ? route('placement.companies.update', $c->id) : route('placement.companies.store') }}">
                @csrf
                @if($isEdit) @method('PUT') @endif
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Company name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $c->name) }}" required maxlength="200">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Industry</label>
                        <select name="industry_id" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($data['industries'] as $id => $name)
                                <option value="{{ $id }}" @selected(old('industry_id', $c->industry_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Season</label>
                        <select name="season_id" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($data['seasons'] as $id => $name)
                                <option value="{{ $id }}" @selected(old('season_id', $c->season_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Website</label>
                        <input type="url" name="website" class="form-control" value="{{ old('website', $c->website) }}" placeholder="https://">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Head office</label>
                        <input type="text" name="hq_location" class="form-control" value="{{ old('hq_location', $c->hq_location) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Logo (jpg/png/webp, max 2 MB)</label>
                        <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                    <div class="col-md-12 form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $c->address) }}</textarea>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>MoU status <span class="text-danger">*</span></label>
                        <select name="mou_status" class="form-control" required>
                            @foreach($data['mou_statuses'] as $key => $label)
                                <option value="{{ $key }}" @selected(old('mou_status', $c->mou_status) == $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>MoU valid till</label>
                        <input type="date" name="mou_valid_till" class="form-control" value="{{ old('mou_valid_till', $c->mou_valid_till) }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>GST no. (optional)</label>
                        <input type="text" name="gst_no" class="form-control" maxlength="30" autocomplete="off"
                               placeholder="{{ $data['has_gst'] ? 'Stored - leave blank to keep' : '' }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>PAN (optional)</label>
                        <input type="text" name="pan_no" class="form-control" maxlength="20" autocomplete="off"
                               placeholder="{{ $data['has_pan'] ? 'Stored - leave blank to keep' : '' }}">
                    </div>
                    <div class="col-md-12 form-group">
                        <label>Past visit history</label>
                        <textarea name="visit_history" class="form-control" rows="3">{{ old('visit_history', $c->visit_history) }}</textarea>
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
