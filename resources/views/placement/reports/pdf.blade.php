<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h1 { font-size: 15px; margin: 0 0 2px; }
        .muted { color: #666; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eee; text-align: left; }
        th, td { border: 1px solid #bbb; padding: 3px 4px; vertical-align: top; }
        .summary span { display: inline-block; margin-right: 14px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="muted">
        {{ $institute }} &middot; Generated {{ now()->format('d-m-Y H:i') }}
        @foreach($filters as $k => $v) &middot; {{ $k }}: {{ $v }} @endforeach
    </div>
    @if(count($summary))
        <div class="summary">@foreach($summary as $k => $v)<span><strong>{{ $k }}:</strong> {{ $v }}</span>@endforeach</div>
    @endif
    <table>
        <thead><tr>@foreach($headings as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
        @forelse($rows as $row)
            <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
        @empty
            <tr><td colspan="{{ count($headings) }}" style="text-align:center">No data for the selected filters.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
