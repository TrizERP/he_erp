<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Recruiter portal - {{ $data['drive']->title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css">
    <style>body{background:#f4f6f9;padding:20px 0}.card{background:#fff;border-radius:4px;padding:16px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.1)}</style>
</head>
<body>
@php
    $d = $data['drive'];
    $yearLabel = fn ($r) => $r;
@endphp
<div class="container">
    <div class="card">
        <h3 style="margin-top:0">{{ $data['company']->name ?? '' }} &mdash; {{ $d->title }}</h3>
        <p class="text-muted">Campus recruitment portal. This link is private to you and expires automatically. Student contact details are shared by the placement cell once you shortlist candidates.</p>
    </div>

    @if(session('done'))<div class="alert alert-success">{{ session('done') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul style="margin:0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <h4 style="margin-top:0">Applicants ({{ $data['applicants']->count() }})</h4>
        <div class="table-responsive">
            <table class="table table-striped table-condensed">
                <thead><tr><th>Name</th><th>Role</th><th>Course</th><th>CGPA</th><th>Skills</th><th>Status</th><th>Resume</th></tr></thead>
                <tbody>
                @forelse($data['applicants'] as $a)
                    <tr>
                        <td>{{ $a->name }}</td><td>{{ $a->role }}</td><td>{{ $a->course }}</td><td>{{ $a->cgpa }}</td><td>{{ $a->skills }}</td>
                        <td>{{ ucfirst($a->status) }}</td>
                        <td>@if($a->resume_url)<a href="{{ $a->resume_url }}" target="_blank" rel="noopener noreferrer">Open</a>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No applicants yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($data['rounds'] as $round)
        @php($rows = $data['results'][$round->id] ?? collect())
        <div class="card">
            <h4 style="margin-top:0">Round {{ $round->sequence }}: {{ $round->name }}
                <small>{{ $round->round_date?->format('d-m-Y') }} {{ $round->venue }}</small>
                @if($round->results_published_at)<span class="label label-success">Results published</span>@endif</h4>
            @if($rows->isEmpty())
                <p class="text-muted">The placement cell has not listed participants for this round yet.</p>
            @else
                <form method="post" action="{{ $data['post_url'] }}">
                    @csrf
                    <input type="hidden" name="round_id" value="{{ $round->id }}">
                    <table class="table table-condensed">
                        <thead><tr><th>Candidate</th><th>Attendance</th><th>Result</th><th>Score</th><th>Remarks</th></tr></thead>
                        <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>{{ $data['names'][$row->application_id]->name ?? 'Candidate' }}</td>
                                <td>{{ ucfirst($row->attendance) }}</td>
                                <td>
                                    <select name="rows[{{ $row->application_id }}][result]" class="form-control input-sm" @disabled($round->results_published_at !== null)>
                                        @foreach(['pending' => 'Pending', 'cleared' => 'Cleared', 'rejected' => 'Rejected', 'on_hold' => 'On hold'] as $k => $l)<option value="{{ $k }}" @selected($row->result == $k)>{{ $l }}</option>@endforeach
                                    </select>
                                </td>
                                <td style="width:90px"><input type="number" step="0.01" name="rows[{{ $row->application_id }}][score]" class="form-control input-sm" value="{{ $row->score }}" @disabled($round->results_published_at !== null)></td>
                                <td><input name="rows[{{ $row->application_id }}][remarks]" class="form-control input-sm" value="{{ $row->remarks }}" @disabled($round->results_published_at !== null)></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @unless($round->results_published_at)<button class="btn btn-primary btn-sm">Save results</button>@endunless
                </form>
            @endif
        </div>
    @endforeach
</div>
</body>
</html>
