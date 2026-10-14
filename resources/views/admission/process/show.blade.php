@php
    $e = $data['applicant']['enquiry'];
    $form = $data['applicant']['form'];
    $wf = $data['workflow'];
    $can = $data['can'];
    $id = $e['id'];
    $locked = $wf->confirmation_status === 'confirmed';
    $guard = "this.querySelector('button[type=submit]').disabled = true;";   // blocks double submission
@endphp
@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    @include('admission.process._style')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                <h4 class="page-title">{{ trim($e['first_name'] . ' ' . $e['middle_name'] . ' ' . $e['last_name']) }} <small>Enquiry {{ $e['enquiry_no'] }}</small></h4>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12 text-right">
                <a href="{{ route('admission.review.index') }}" class="btn btn-default"><i class="fa fa-angle-left"></i> Back to list</a>
            </div>
        </div>

        @include('admission.process._flash')

        {{-- Who + where it is now ---------------------------------------------------------------------- --}}
        <div class="card">
            <div class="row">
                <div class="col-md-3 col-sm-6"><small class="text-muted">Programme</small><br><strong>{{ $data['standard'] ?? 'Not set' }}</strong></div>
                <div class="col-md-2 col-sm-6"><small class="text-muted">Mobile</small><br>{{ $e['mobile'] }}</div>
                <div class="col-md-2 col-sm-6"><small class="text-muted">Form no.</small><br>{{ $form['form_no'] ?? '-' }}</div>
                <div class="col-md-5 col-sm-6"><small class="text-muted">Where it is now</small><br>
                    <span class="label label-{{ $data['stage']['color'] }}" style="font-size:14px">{{ $data['stage']['label'] }}</span></div>
            </div>
            @if ($data['student'])
                <hr style="margin:10px 0">
                <i class="fa fa-graduation-cap text-success"></i> <strong>Student record created:</strong> ID {{ $data['student']->id }}, enrollment no. {{ $data['student']->enrollment_no }}
                ({{ trim($data['student']->first_name . ' ' . $data['student']->last_name) }})
            @endif
        </div>

        {{-- Progress ---------------------------------------------------------------------------------------- --}}
        <div class="card">
            <ul class="adm-steps">
                @foreach ($data['steps'] as $i => $step)
                    <li class="{{ $step['state'] }}">
                        <a href="#{{ $step['anchor'] }}" title="Go to {{ $step['label'] }}">
                            <div class="dot">@if ($step['state'] === 'done')<i class="fa fa-check"></i>@else{{ $i + 1 }}@endif</div>
                            {{ $step['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="adm-next {{ $data['stage']['tone'] }}">
            <div class="k">{{ $data['stage']['tone'] === 'done' ? 'Finished' : 'Next step' }}</div>
            <div class="t">{{ $data['stage']['label'] }}</div>
            <p class="d">{{ $data['stage']['next'] }}</p>
            <a href="#{{ $data['stage']['anchor'] }}" class="btn btn-primary btn-sm">Go to this step <i class="fa fa-arrow-down"></i></a>
        </div>

        {{-- 1. Inquiry / application -------------------------------------------------------------- --}}
        <div class="card adm-sec" id="sec-application">
            <h4><span class="n">1</span>Application</h4>
            <p class="adm-help">An inquiry becomes an application once its form is created. Submit it when all details and documents are complete.</p>
            @if (! $form)
                <p>The application form has not been created yet.</p>
                @if ($can['manage'])
                    <form method="POST" action="{{ route('admission.process.convert', $id) }}" onsubmit="{{ $guard }}" style="display:inline">@csrf
                        <button type="submit" class="btn btn-success">Convert inquiry to application</button></form>
                @endif
            @elseif (in_array($wf->application_status, ['draft', 'reopened']))
                @if ($data['gaps'])
                    <div class="alert alert-warning"><strong>Before this can be submitted:</strong>
                        <ul style="margin:6px 0 0 18px">@foreach ($data['gaps'] as $gap)<li>{{ $gap }}</li>@endforeach</ul></div>
                @else
                    <p class="text-success">All required fields and documents are present.</p>
                @endif
                @if ($can['manage'])
                    <form method="POST" action="{{ route('admission.process.submit', $id) }}" onsubmit="if (! confirm('Submit this application?')) { return false; } {{ $guard }}">@csrf
                        <button type="submit" class="btn btn-success" @disabled($data['gaps'])>Submit application</button></form>
                @endif
            @else
                <p>{{ $wf->submitted_at ? 'Submitted on ' . $wf->submitted_at->format('d M Y H:i') : 'Submitted' }}.</p>
                @if ($can['manage'] && ! $locked)
                    <form method="POST" action="{{ route('admission.process.reopen', $id) }}" class="form-inline" onsubmit="if (! confirm('Reopen this application for correction?')) { return false; } {{ $guard }}">@csrf
                        <input type="text" name="reason" class="form-control" placeholder="Reason for reopening" required minlength="3" maxlength="500" style="width:320px">
                        <button type="submit" class="btn btn-warning">Reopen for correction</button></form>
                @endif
            @endif

            @if ($can['manage'] && ! $locked)
                <form method="POST" action="{{ route('admission.process.inquiry_status', $id) }}" class="form-inline" style="margin-top:10px" onsubmit="{{ $guard }}">@csrf
                    <label>Inquiry status</label>
                    <select name="status" class="form-control">
                        @foreach (['new' => 'New', 'contacted' => 'Contacted', 'follow_up' => 'Follow-up', 'closed' => 'Closed'] as $value => $label)
                            <option value="{{ $value }}" @selected($wf->inquiry_status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="note" class="form-control" placeholder="Note (optional)" maxlength="500">
                    <button type="submit" class="btn btn-default">Update</button>
                </form>
            @endif
        </div>

        {{-- 2. Documents ----------------------------------------------------------------------------- --}}
        <div class="card adm-sec" id="sec-documents">
            <h4><span class="n">2</span>Documents</h4>
            <p class="adm-help">Upload each required certificate. A person then checks it and marks it <b>Verified</b>, or asks for a re-upload with a reason. A star (*) means the document is mandatory.</p>
            @if ($data['documents']->isEmpty() && $data['requirements']->isEmpty())
                <p class="text-muted">No required documents are configured for this programme. Add them in Admission Settings.</p>
            @endif
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>Document</th><th>Status</th><th>File</th><th>Reason</th><th style="width:34%">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($data['documents'] as $doc)
                        @php $req = $data['requirements']->firstWhere('id', $doc->requirement_id); @endphp
                        <tr>
                            <td>{{ $doc->doc_name }} @if ($req && $req->is_mandatory)<span class="text-danger" title="Mandatory">*</span>@endif</td>
                            <td>@include('admission.process._badge', ['value' => $doc->status])</td>
                            <td>
                                @if ($doc->storage_path && ($can['manage'] || $can['documents'] || $can['decide']))
                                    <a href="{{ route('admission.process.document_download', $doc->id) }}" target="_blank" rel="noopener">{{ $doc->original_filename }}</a>
                                    <small class="text-muted">v{{ $doc->version }}</small>
                                @else - @endif
                            </td>
                            <td>{{ $doc->rejection_reason }}</td>
                            <td>
                                @if (! $locked)
                                    @if ($can['manage'] && $doc->status !== 'verified')
                                        <form method="POST" action="{{ route('admission.process.document_upload', $id) }}" enctype="multipart/form-data" class="form-inline" style="display:inline" onsubmit="{{ $guard }}">@csrf
                                            <input type="hidden" name="requirement_id" value="{{ $doc->requirement_id }}">
                                            <input type="file" name="file" required style="display:inline-block;width:170px">
                                            <button type="submit" class="btn btn-xs btn-info">{{ $doc->status === 'pending' ? 'Upload' : 'Replace' }}</button>
                                        </form>
                                    @endif
                                    @if ($can['documents'] && $doc->status === 'uploaded')
                                        <form method="POST" action="{{ route('admission.process.document_verify', $doc->id) }}" style="display:inline" onsubmit="{{ $guard }}">@csrf
                                            <button type="submit" class="btn btn-xs btn-success">Verify</button></form>
                                    @endif
                                    @if ($can['documents'] && in_array($doc->status, ['uploaded', 'verified']))
                                        <form method="POST" action="{{ route('admission.process.document_reject', $doc->id) }}" class="form-inline" style="display:inline" onsubmit="if (! confirm('Send this document back?')) { return false; } {{ $guard }}">@csrf
                                            <input type="text" name="reason" class="form-control input-sm" placeholder="Reason" required minlength="3" maxlength="500" style="width:120px">
                                            <select name="mode" class="form-control input-sm"><option value="resubmit">Ask to resubmit</option><option value="reject">Reject</option></select>
                                            <button type="submit" class="btn btn-xs btn-danger">Send back</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if ($can['manage'] && ! $locked)
                <form method="POST" action="{{ route('admission.process.document_upload', $id) }}" enctype="multipart/form-data" class="form-inline" onsubmit="{{ $guard }}">@csrf
                    <label>Other document</label>
                    <input type="text" name="doc_name" class="form-control" placeholder="Document name" required maxlength="120">
                    <input type="file" name="file" class="form-control" required>
                    <button type="submit" class="btn btn-default">Upload</button>
                    <small class="text-muted">PDF, JPG or PNG up to {{ config('admission.upload.default_max_kb') }} KB unless the document type says otherwise.</small>
                </form>
            @endif
        </div>

        {{-- 3. Eligibility ----------------------------------------------------------------------------- --}}
        <div class="card adm-sec" id="sec-eligibility">
            <h4><span class="n">3</span>Eligibility check</h4>
            <p class="adm-help">Compares the applicant with your approved criteria (set in Settings). If a detail is missing, the applicant is <b>not rejected</b> - a person decides.</p>
            <p>Recorded status: @include('admission.process._badge', ['value' => $wf->eligibility_status])
                @if ($wf->eligibility_override) <span class="label label-warning">Manual override</span> @endif
                <br><small>{{ $wf->eligibility_reason }}</small></p>

            <strong>Check against the configured criteria now:</strong>
            <span class="text-muted">- {{ $data['evaluation']['reason'] }}</span>
            @if ($data['evaluation']['values'])
                <div class="table-responsive"><table class="table table-condensed">
                    <thead><tr><th>Criterion</th><th>Rule</th><th>Applicant value</th><th>Result</th></tr></thead>
                    <tbody>
                    @foreach ($data['evaluation']['values'] as $row)
                        <tr>
                            <td>{{ $row['label'] }} @if (! $row['mandatory'])<small class="text-muted">(informational)</small>@endif</td>
                            <td>{{ $row['field'] }} {{ $row['operator'] }} {{ $row['expected'] }}</td>
                            <td>{{ $row['actual'] === null || $row['actual'] === '' ? 'missing' : $row['actual'] }}</td>
                            <td>@include('admission.process._badge', ['value' => ['pass' => 'eligible', 'fail' => 'ineligible', 'missing' => 'needs_review'][$row['outcome']]])</td>
                        </tr>
                    @endforeach
                    </tbody></table></div>
            @endif

            @if (! $locked)
                @if ($can['manage'])
                    <form method="POST" action="{{ route('admission.process.eligibility_run', $id) }}" style="display:inline" onsubmit="{{ $guard }}">@csrf
                        <button type="submit" class="btn btn-info">Evaluate and save</button></form>
                @endif
                @if ($can['override'])
                    @if ($wf->eligibility_override)
                        <form method="POST" action="{{ route('admission.process.eligibility_run', $id) }}" style="display:inline" onsubmit="if (! confirm('Remove the manual decision and re-evaluate?')) { return false; } {{ $guard }}">@csrf
                            <input type="hidden" name="reset_override" value="1"><button type="submit" class="btn btn-default">Clear override and re-evaluate</button></form>
                    @endif
                    <form method="POST" action="{{ route('admission.process.eligibility_override', $id) }}" class="form-inline" style="margin-top:10px" onsubmit="if (! confirm('Record a manual eligibility decision? This is audited.')) { return false; } {{ $guard }}">@csrf
                        <label>Manual decision (audited)</label>
                        <select name="status" class="form-control">
                            @foreach (['eligible' => 'Eligible', 'ineligible' => 'Ineligible', 'needs_review' => 'Needs review'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                        <input type="text" name="reason" class="form-control" placeholder="Reason (required)" required minlength="5" maxlength="500" style="width:300px">
                        <button type="submit" class="btn btn-warning">Record</button>
                    </form>
                @endif
            @endif
        </div>

        {{-- 4. Selection ---------------------------------------------------------------------------------- --}}
        <div class="card adm-sec" id="sec-selection">
            <h4><span class="n">4</span>Selection decision</h4>
            <p class="adm-help">Record the review result: shortlist, waitlist, select or reject.</p>
            <p>Current: @include('admission.process._badge', ['value' => $wf->selection_status ?? 'under_review'])
                <small>{{ $wf->selection_remarks }}</small></p>
            <p class="text-muted"><small>Selection is a recorded decision. No merit formula or tie-break has been approved, so none is applied automatically.</small></p>
            @if ($can['decide'] && ! $locked && $wf->application_status === 'submitted')
                <form method="POST" action="{{ route('admission.process.selection', $id) }}" class="form-inline" onsubmit="if (! confirm('Record this decision?')) { return false; } {{ $guard }}">@csrf
                    <select name="status" class="form-control">
                        @foreach (\App\Models\admission\AdmissionWorkflow::SELECTION as $value => $label)
                            <option value="{{ $value }}" @selected(($wf->selection_status ?? 'under_review') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="remarks" class="form-control" placeholder="Remarks (required for reject / waitlist)" maxlength="500" style="width:340px">
                    <button type="submit" class="btn btn-primary">Record decision</button>
                </form>
            @endif
        </div>

        {{-- 5. Offer ---------------------------------------------------------------------------------------- --}}
        <div class="card adm-sec" id="sec-offer">
            <h4><span class="n">5</span>Offer / seat allocation</h4>
            <p class="adm-help">Send an offer with a reply-by date. Record the applicant's answer here. Only one live offer is allowed at a time.</p>
            <div class="table-responsive"><table class="table table-condensed">
                <thead><tr><th>Offer date</th><th>Respond by</th><th>Seat category</th><th>Status</th><th>Notes</th><th></th></tr></thead>
                <tbody>
                @forelse ($data['offers'] as $offer)
                    <tr>
                        <td>{{ $offer->offer_date }}</td><td>{{ $offer->expiry_date }}</td><td>{{ $offer->student_quota ?? '-' }}</td>
                        <td>@include('admission.process._badge', ['value' => $offer->status])</td><td>{{ $offer->notes }}</td>
                        <td>
                            @if ($can['offer'] && ! $locked)
                                @if ($offer->status === 'issued')
                                    <form method="POST" action="{{ route('admission.process.offer_respond', $offer->id) }}" style="display:inline" onsubmit="{{ $guard }}">@csrf
                                        <input type="hidden" name="decision" value="accept"><button type="submit" class="btn btn-xs btn-success">Mark accepted</button></form>
                                    <form method="POST" action="{{ route('admission.process.offer_respond', $offer->id) }}" style="display:inline" onsubmit="if (! confirm('Mark this offer as declined?')) { return false; } {{ $guard }}">@csrf
                                        <input type="hidden" name="decision" value="decline"><button type="submit" class="btn btn-xs btn-default">Mark declined</button></form>
                                @endif
                                @if (in_array($offer->status, ['issued', 'accepted']))
                                    <form method="POST" action="{{ route('admission.process.offer_withdraw', $offer->id) }}" class="form-inline" style="display:inline" onsubmit="if (! confirm('Withdraw this offer?')) { return false; } {{ $guard }}">@csrf
                                        <input type="text" name="reason" class="form-control input-sm" placeholder="Reason" required minlength="3" maxlength="500" style="width:110px">
                                        <button type="submit" class="btn btn-xs btn-danger">Withdraw</button></form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No offers yet.</td></tr>
                @endforelse
                </tbody></table></div>
            @if ($can['offer'] && ! $locked && $wf->application_status === 'submitted' && ! $data['offers']->whereIn('status', ['issued', 'accepted'])->count())
                <form method="POST" action="{{ route('admission.process.offer_issue', $id) }}" class="form-inline" onsubmit="{{ $guard }}">@csrf
                    <label>Issue offer</label>
                    <input type="date" name="offer_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    <input type="date" name="expiry_date" class="form-control" min="{{ now()->toDateString() }}" required title="Respond by">
                    <select name="standard_id" class="form-control">
                        @foreach ($data['standards'] as $standard)<option value="{{ $standard->id }}" @selected($standard->id == $wf->standard_id)>{{ $standard->name }}</option>@endforeach
                    </select>
                    <input type="text" name="student_quota" class="form-control" placeholder="Seat category (optional)" maxlength="50">
                    <input type="text" name="notes" class="form-control" placeholder="Notes" maxlength="500">
                    <button type="submit" class="btn btn-success">Issue offer</button>
                </form>
            @endif
        </div>

        {{-- 6. Fee ------------------------------------------------------------------------------------------- --}}
        <div class="card adm-sec" id="sec-fee">
            <h4><span class="n">6</span>Admission fee</h4>
            <p class="adm-help">Read-only. The amount due comes from the fee structure and payments come from the admission registration record.</p>
            @php $fee = $data['fee']; @endphp
            <p>Status @include('admission.process._badge', ['value' => $fee['status']])
                &nbsp; Due: <strong>{{ number_format($fee['demand'], 2) }}</strong>
                &nbsp; Paid: <strong>{{ number_format($fee['paid'], 2) }}</strong>
                &nbsp; Balance: <strong>{{ number_format($fee['balance'], 2) }}</strong>
                @if ($fee['payment_mode']) &nbsp; <small>({{ $fee['payment_mode'] }} {{ $fee['date_of_payment'] }})</small> @endif</p>
            <p class="text-muted"><small>Read from the existing fee structure and the payment recorded on the admission registration record. Payments cannot be marked here.
                    <a href="{{ route('admission_confirmation.edit', $id) }}">Open the admission registration screen to record a payment.</a></small></p>

            @php
                $hasEnquiryReceipt = trim((string) ($e['receipt_html'] ?? '')) !== '';
                $hasFormReceipt = trim((string) ($form['receipt_html'] ?? '')) !== '';
            @endphp
            <strong><i class="fa fa-print"></i> Receipts</strong>
            <div style="margin:6px 0 4px">
                @if ($fee['paid'] > 0)
                    <a href="{{ route('admission.process.receipt', [$id, 'registration']) }}" target="_blank" rel="noopener" class="btn btn-sm btn-success"><i class="fa fa-print"></i> Admission fee receipt ({{ number_format($fee['paid'], 2) }})</a>
                @endif
                @if ($hasEnquiryReceipt)
                    <a href="{{ route('admission.process.receipt', [$id, 'enquiry']) }}" target="_blank" rel="noopener" class="btn btn-sm btn-info"><i class="fa fa-file-text-o"></i> Inquiry fee receipt{{ ! empty($e['receipt_id']) ? ' #' . $e['receipt_id'] : '' }}</a>
                @endif
                @if ($hasFormReceipt)
                    <a href="{{ route('admission.process.receipt', [$id, 'form']) }}" target="_blank" rel="noopener" class="btn btn-sm btn-info"><i class="fa fa-file-text-o"></i> Application form fee receipt{{ ! empty($form['receipt_id']) ? ' #' . $form['receipt_id'] : '' }}</a>
                @endif
                @if (! $hasEnquiryReceipt && ! $hasFormReceipt && $fee['paid'] <= 0)
                    <span class="text-muted">No receipt yet: no fee payment has been recorded for this applicant.</span>
                @endif
            </div>
            <p class="adm-sub">The green button prints a receipt for the payment above (from the admission registration). The blue buttons reopen receipts the admission screens created earlier for the inquiry fee / form fee.</p>
            @if ($fee['waived']) <p><span class="label label-info">Waived</span> {{ $fee['waiver_reason'] }}</p> @endif
            @if ($can['waive'] && ! $locked && ! $fee['waived'] && ($data['settings']['allow_fee_waiver'] ?? '0') === '1')
                <form method="POST" action="{{ route('admission.process.fee_waive', $id) }}" class="form-inline" onsubmit="if (! confirm('Waive the admission fee? This is audited.')) { return false; } {{ $guard }}">@csrf
                    <input type="text" name="reason" class="form-control" placeholder="Waiver reason (required)" required minlength="3" maxlength="500" style="width:320px">
                    <button type="submit" class="btn btn-warning">Waive fee</button></form>
            @endif
        </div>

        {{-- 7. Confirmation -------------------------------------------------------------------------------------- --}}
        <div class="card adm-sec" id="sec-confirm">
            <h4><span class="n">7</span>Confirmation checklist</h4>
            <p class="adm-help">Everything marked <span class="label label-danger">Blocking</span> must be fixed before the admission can be confirmed. Confirming creates the student record - once.</p>
            <div class="table-responsive"><table class="table table-condensed">
                <thead><tr><th>Check</th><th>Needed</th><th>Result</th><th>Detail</th></tr></thead>
                <tbody>
                @foreach ($data['checklist']['items'] as $item)
                    <tr>
                        <td>{{ $item['label'] }}</td>
                        <td>{{ $item['required'] ? 'Required' : 'Not required' }}</td>
                        <td>@if ($item['ok'])<span class="label label-success">OK</span>@elseif ($item['required'])<span class="label label-danger">Blocking</span>@else<span class="label label-default">Not met</span>@endif</td>
                        <td><small>{{ $item['detail'] }}</small></td>
                    </tr>
                @endforeach
                </tbody></table></div>

            @if ($locked)
                <div class="alert alert-success">Admission confirmed {{ optional($wf->confirmed_at)->format('d M Y H:i') }}.
                    @if ($wf->confirmation_override) Confirmed with an authorised override: {{ $wf->confirmation_override_reason }} @endif</div>
            @elseif ($can['confirm'])
                <form method="POST" action="{{ route('admission.process.confirm', $id) }}" class="form-inline" onsubmit="if (! confirm('Confirm this admission and create the student record? This cannot be undone.')) { return false; } {{ $guard }}">@csrf
                    @if ($data['checklist']['blocking'] && $data['checklist']['can_override'] && $can['override'])
                        <input type="text" name="override_reason" class="form-control" placeholder="Override reason (required to bypass blocking checks)" maxlength="500" style="width:420px">
                    @endif
                    <button type="submit" class="btn btn-success" @disabled($data['checklist']['blocking'] && ! ($data['checklist']['can_override'] && $can['override']))>Confirm admission</button>
                </form>
                @if ($wf->confirmation_status === 'cancelled')
                    <p class="text-danger" style="margin-top:8px">This admission is cancelled: {{ $wf->confirmation_note }}</p>
                @endif
                <form method="POST" action="{{ route('admission.process.confirmation_status', $id) }}" class="form-inline" style="margin-top:10px" onsubmit="if (! confirm('Change the admission status?')) { return false; } {{ $guard }}">@csrf
                    <select name="status" class="form-control">
                        <option value="on_hold">Put on hold</option><option value="cancelled">Cancel admission</option><option value="pending">Resume</option>
                    </select>
                    <input type="text" name="reason" class="form-control" placeholder="Reason (required)" required minlength="3" maxlength="500" style="width:300px">
                    <button type="submit" class="btn btn-default">Apply</button>
                </form>
            @endif
        </div>

        {{-- History ---------------------------------------------------------------------------------------------- --}}
        <div class="card">
            <h4>History</h4>
            <p class="adm-help">Everything that happened to this application, newest first.</p>
            <div class="table-responsive"><table class="table table-condensed table-striped">
                <thead><tr><th>When</th><th>Action</th><th>From</th><th>To</th><th>Comment</th><th>By</th></tr></thead>
                <tbody>
                @forelse ($data['history'] as $h)
                    <tr>
                        <td>{{ optional($h->created_at)->format('d M Y H:i') }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $h->action)) }} @if ($h->is_override)<span class="label label-warning">override</span>@endif</td>
                        <td>{{ $h->from_status }}</td><td>{{ $h->to_status }}</td><td>{{ $h->comments }}</td><td>#{{ $h->user_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No history yet.</td></tr>
                @endforelse
                </tbody></table></div>
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
