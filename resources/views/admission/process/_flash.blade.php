@if ($sessionData = Session::get('data'))
    <div class="alert {{ ($sessionData['status_code'] ?? 0) == 1 ? 'alert-success' : 'alert-danger' }} alert-block">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>{{ $sessionData['message'] ?? '' }}</strong>
        @if (! empty($sessionData['details']))
            <ul style="margin:6px 0 0 18px">
                @foreach ($sessionData['details'] as $detail)
                    <li>{{ $detail }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following:</strong>
        <ul style="margin:6px 0 0 18px">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
