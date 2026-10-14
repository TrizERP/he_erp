@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title"><div class="col-lg-12"><h4 class="page-title">Notices &amp; Notifications</h4></div></div>

        <div class="row">
            <div class="col-md-7">
                <h5>Notice board</h5>
                @forelse($data['notices'] as $n)
                    <div class="card">
                        <h5 style="margin-top:0">@if($n->is_pinned)<i class="fa fa-thumb-tack text-danger"></i> @endif{{ $n->title }}</h5>
                        <p>{!! nl2br(e($n->body)) !!}</p>
                        <small class="text-muted">{{ ($n->publish_at ?? $n->created_at)->format('d-m-Y H:i') }}</small>
                    </div>
                @empty
                    <div class="card text-center text-muted">No notices.</div>
                @endforelse
            </div>
            <div class="col-md-5">
                <h5>My notifications</h5>
                @forelse($data['inbox'] as $m)
                    <div class="card" style="padding:10px">
                        <div>{!! nl2br(e($m->body)) !!}</div>
                        <small class="text-muted">{{ $m->created_at->format('d-m-Y H:i') }} @if(! $m->read_at)<span class="label label-info">new</span>@endif</small>
                    </div>
                @empty
                    <div class="card text-center text-muted">Nothing here yet.</div>
                @endforelse
            </div>
        </div>
    </div>
    @include('includes.footerJs')
    @include('includes.footer')
</div>
