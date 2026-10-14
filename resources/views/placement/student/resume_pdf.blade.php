<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resume - {{ $student->name ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 22px; margin: 0; }
        h2 { font-size: 14px; border-bottom: 1px solid #888; padding-bottom: 2px; margin: 16px 0 6px; text-transform: uppercase; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 4px; vertical-align: top; }
    </style>
</head>
<body>
    <h1>{{ $student->name ?? '' }}</h1>
    <div class="muted">
        {{ $student->standard_name ?? '' }} &middot; Enrollment {{ $student->enrollment_no ?? '' }}
        @if(! empty($student->email)) &middot; {{ $student->email }} @endif
        @if(! empty($student->mobile)) &middot; {{ $student->mobile }} @endif
    </div>
    <div class="muted">
        @foreach(['linkedin_url' => 'LinkedIn', 'github_url' => 'GitHub', 'portfolio_url' => 'Portfolio'] as $f => $l)
            @if($profile->{$f}) {{ $l }}: {{ $profile->{$f} }} &nbsp; @endif
        @endforeach
    </div>

    <h2>Education</h2>
    <table>
        @if($profile->cgpa !== null)<tr><td>Current course CGPA</td><td>{{ $profile->cgpa }} / 10</td></tr>@endif
        @if($profile->twelfth_percent !== null)<tr><td>12th</td><td>{{ $profile->twelfth_percent }}%</td></tr>@endif
        @if($profile->tenth_percent !== null)<tr><td>10th</td><td>{{ $profile->tenth_percent }}%</td></tr>@endif
        @if($profile->passing_year)<tr><td>Expected graduation</td><td>{{ $profile->passing_year }}</td></tr>@endif
    </table>

    @if($profile->skills->count())
        <h2>Skills</h2>
        <p>{{ $profile->skills->map(fn ($k) => $k->skill_name.' ('.ucfirst($k->level).')')->implode(', ') }}</p>
    @endif

    @if(! empty($profile->projects))
        <h2>Projects</h2>
        @foreach($profile->projects as $x)<p><strong>{{ $x['title'] }}</strong><br>{{ $x['description'] ?? '' }}</p>@endforeach
    @endif

    @if(! empty($profile->certifications))
        <h2>Certifications</h2>
        @foreach($profile->certifications as $x)<div>{{ $x['title'] }}@if(! empty($x['issuer'])) &mdash; {{ $x['issuer'] }}@endif</div>@endforeach
    @endif

    @if(! empty($profile->achievements))
        <h2>Achievements</h2>
        <ul>@foreach($profile->achievements as $x)<li>{{ $x }}</li>@endforeach</ul>
    @endif
</body>
</html>
