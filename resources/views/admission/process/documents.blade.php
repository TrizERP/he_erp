@php $can = fn ($c) => \App\Services\Admission\AdmissionAccess::can($c); @endphp
@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    @include('admission.process._style')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12"><h4 class="page-title">Document Verification</h4></div>
        </div>

        @include('admission.process._flash')

        <p class="adm-intro">Certificates uploaded for applicants. Open a file to read it, then press <b>Verify</b> - or send it back with a reason so the applicant uploads it again. The list starts with documents <b>waiting for your check</b>.</p>

        <div class="card">
            <form method="GET" class="form-inline" style="margin-bottom:12px">
                <label>Status</label>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="all" @selected($data['status'] === 'all')>All</option>
                    @foreach ($data['statuses'] as $value => $label)
                        <option value="{{ $value }}" @selected($data['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <div class="table-responsive">
                <table class="table table-hover adm-table">
                    <thead><tr><th>Enquiry no.</th><th>Applicant</th><th>Document</th><th>Status</th><th>File</th><th>Reason</th><th>Updated</th><th style="width:28%">Action</th></tr></thead>
                    <tbody>
                    @forelse ($data['rows'] as $doc)
                        <tr>
                            <td><a href="{{ route('admission.review.show', $doc->enquiry_id) }}">{{ $doc->enquiry_no }}</a></td>
                            <td>{{ trim($doc->first_name . ' ' . $doc->last_name) }}</td>
                            <td>{{ $doc->doc_name }}</td>
                            <td>@include('admission.process._badge', ['value' => $doc->status])</td>
                            <td>@if ($doc->storage_path)<a href="{{ route('admission.process.document_download', $doc->id) }}" target="_blank" rel="noopener">{{ $doc->original_filename }}</a> <small class="text-muted">v{{ $doc->version }}</small>@else - @endif</td>
                            <td>{{ $doc->rejection_reason }}</td>
                            <td><small>{{ $doc->updated_at }}</small></td>
                            <td>
                                @if ($can('documents'))
                                    @if ($doc->status === 'uploaded')
                                        <form method="POST" action="{{ route('admission.process.document_verify', $doc->id) }}" style="display:inline" onsubmit="this.querySelector('button').disabled = true;">@csrf
                                            <button class="btn btn-xs btn-success">Verify</button></form>
                                    @endif
                                    @if (in_array($doc->status, ['uploaded', 'verified']))
                                        <form method="POST" action="{{ route('admission.process.document_reject', $doc->id) }}" class="form-inline" style="display:inline" onsubmit="if (! confirm('Send this document back?')) { return false; } this.querySelector('button').disabled = true;">@csrf
                                            <input type="text" name="reason" class="form-control input-sm" placeholder="Reason" required minlength="3" maxlength="500" style="width:110px">
                                            <select name="mode" class="form-control input-sm"><option value="resubmit">Resubmit</option><option value="reject">Reject</option></select>
                                            <button class="btn btn-xs btn-danger">Send back</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No documents in this status.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data['rows']->links() }}
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
