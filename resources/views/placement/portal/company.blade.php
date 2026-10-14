<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Post a requirement - {{ $data['company']->name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css">
    <style>body{background:#f4f6f9;padding:20px 0}.card{background:#fff;border-radius:4px;padding:16px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.1)}</style>
</head>
<body>
<div class="container" style="max-width:760px">
    <div class="card">
        <h3 style="margin-top:0">{{ $data['company']->name }} &mdash; campus requirement</h3>
        <p class="text-muted">Tell the placement cell what you are hiring for. They will review it and set up a drive with you.</p>
    </div>
    @if(session('done'))<div class="alert alert-success">{{ session('done') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul style="margin:0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <div class="card">
        <form method="post" action="{{ $data['post_url'] }}">
            @csrf
            <div class="row">
                <div class="col-sm-6 form-group"><label>Your name *</label><input name="contact_name" class="form-control" required maxlength="150" value="{{ old('contact_name') }}"></div>
                <div class="col-sm-6 form-group"><label>Your email *</label><input type="email" name="contact_email" class="form-control" required value="{{ old('contact_email') }}"></div>
                <div class="col-sm-12 form-group"><label>Role / position *</label><input name="title" class="form-control" required maxlength="200" value="{{ old('title') }}"></div>
                <div class="col-sm-4 form-group"><label>Openings</label><input type="number" min="1" name="openings" class="form-control" value="{{ old('openings') }}"></div>
                <div class="col-sm-4 form-group"><label>CTC range</label><input name="ctc_range" class="form-control" placeholder="e.g. 6-8 LPA" value="{{ old('ctc_range') }}"></div>
                <div class="col-sm-4 form-group"><label>Key skills</label><input name="skills" class="form-control" value="{{ old('skills') }}"></div>
                <div class="col-sm-12 form-group"><label>Job description</label><textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea></div>
                <div class="col-sm-12"><button class="btn btn-primary">Submit requirement</button></div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
