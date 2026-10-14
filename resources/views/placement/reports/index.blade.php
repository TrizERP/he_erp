@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title"><div class="col-lg-12"><h4 class="page-title">Placement Reports</h4></div></div>
        <div class="row">
            @foreach($data['reports'] as $key => $r)
                <div class="col-md-4 col-sm-6">
                    <div class="card" style="min-height:120px">
                        <h5 style="margin-top:0"><a href="{{ route('placement.reports.show', $key) }}">{{ $r['title'] }}</a></h5>
                        <p class="text-muted">{{ $r['description'] }}</p>
                        <a class="btn btn-info btn-xs" href="{{ route('placement.reports.show', $key) }}">Open</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
