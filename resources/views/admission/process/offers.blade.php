@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    @include('admission.process._style')
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-12"><h4 class="page-title">Offer / Seat Allocation</h4></div>
        </div>

        @include('admission.process._flash')

        <p class="adm-intro">Offers sent to applicants and their replies. To send a new offer, open the applicant (Application Review) and use <b>step 5, Offer</b>. Offers past their reply-by date expire by themselves.</p>

        <div class="row">
            @foreach ($data['statuses'] as $key => $label)
                <div class="col-md-2 col-sm-4 col-xs-6">
                    <a href="{{ route('admission.offers.index', ['status' => $key]) }}" style="text-decoration:none">
                        <div class="card text-center" style="padding:10px"><h3 style="margin:0">{{ $data['counts'][$key] ?? 0 }}</h3><small>{{ $label }}</small></div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="card">
            <p class="text-muted">Offers are issued from the application page after review and selection.
                @if ($data['status']) Filtered: {{ $data['statuses'][$data['status']] ?? '' }} - <a href="{{ route('admission.offers.index') }}">clear</a>@endif</p>
            <div class="table-responsive">
                <table class="table table-hover adm-table">
                    <thead><tr><th>Enquiry no.</th><th>Applicant</th><th>Programme</th><th>Offer date</th><th>Respond by</th><th>Seat category</th><th>Status</th><th>Responded</th></tr></thead>
                    <tbody>
                    @forelse ($data['rows'] as $offer)
                        <tr>
                            <td><a href="{{ route('admission.review.show', $offer->enquiry_id) }}">{{ $offer->enquiry_no }}</a></td>
                            <td>{{ trim($offer->first_name . ' ' . $offer->last_name) }}</td>
                            <td>{{ $offer->standard_name ?? '-' }}</td>
                            <td>{{ $offer->offer_date }}</td>
                            <td>{{ $offer->expiry_date }}</td>
                            <td>{{ $offer->student_quota ?? '-' }}</td>
                            <td>@include('admission.process._badge', ['value' => $offer->status])</td>
                            <td><small>{{ $offer->responded_at }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No offers.</td></tr>
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
