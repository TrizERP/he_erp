@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    @include('admission.process._style')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12"><h4 class="page-title">Admission Fee</h4></div>
        </div>

        @include('admission.process._flash')

        <p class="adm-intro">How much each applicant owes and has paid for admission. This page is for looking only - payments are recorded on the admission registration screen (use the <b>Payment / receipt</b> button).</p>

        <div class="card">
            <p class="text-muted">Fee due comes from the fee structure; payments are read from the admission registration record. Nothing on this page can mark a payment as received.</p>
            <form method="GET" class="form-inline" style="margin-bottom:12px">
                <input type="text" name="q" value="{{ $data['q'] }}" class="form-control" placeholder="Name or enquiry no.">
                <button class="btn btn-success">Search</button>
            </form>
            <div class="table-responsive">
                <table class="table table-hover adm-table">
                    <thead><tr><th>Enquiry no.</th><th>Applicant</th><th>Programme</th><th class="text-right">Due</th><th class="text-right">Paid</th><th class="text-right">Balance</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($data['rows'] as $row)
                        @php $fee = $data['fees'][$row->id]; @endphp
                        <tr>
                            <td>{{ $row->enquiry_no }}</td>
                            <td>{{ trim($row->first_name . ' ' . $row->last_name) }}</td>
                            <td>{{ $row->standard_name ?? '-' }}</td>
                            <td class="text-right">{{ number_format($fee['demand'], 2) }}</td>
                            <td class="text-right">{{ number_format($fee['paid'], 2) }}</td>
                            <td class="text-right">{{ number_format($fee['balance'], 2) }}</td>
                            <td>@include('admission.process._badge', ['value' => $fee['status']])</td>
                            <td style="white-space:nowrap"><a href="{{ route('admission.review.show', $row->id) }}" class="btn btn-xs btn-primary">Open</a>
                                <a href="{{ route('admission_confirmation.edit', $row->id) }}" class="btn btn-xs btn-default">Record payment</a>
                                @if ($fee['paid'] > 0)<a href="{{ route('admission.process.receipt', [$row->id, 'registration']) }}" target="_blank" rel="noopener" class="btn btn-xs btn-success"><i class="fa fa-print"></i> Fee receipt</a>@endif
                                @if ($row->has_enquiry_receipt)<a href="{{ route('admission.process.receipt', [$row->id, 'enquiry']) }}" target="_blank" rel="noopener" class="btn btn-xs btn-info"><i class="fa fa-print"></i> Inquiry receipt</a>@endif
                                @if ($row->has_form_receipt)<a href="{{ route('admission.process.receipt', [$row->id, 'form']) }}" target="_blank" rel="noopener" class="btn btn-xs btn-info"><i class="fa fa-print"></i> Form receipt</a>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No registered applications yet.</td></tr>
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
