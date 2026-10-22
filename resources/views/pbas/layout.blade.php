@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        {{-- ERP page title bar; the ERP breadcrumb above already links Home / PBAS --}}
        <div class="row bg-title">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <h4 class="page-title" style="font-size:20px;line-height:1.4">@yield('heading')</h4>
                <small class="text-muted">@yield('crumb')</small>
            </div>
        </div>

        @yield('alerts')
        @if(session('pbas_success'))
            <div class="alert alert-success alert-block">{{ session('pbas_success') }}</div>
        @endif
        @if(session('pbas_error'))
            <div class="alert alert-danger alert-block">{{ session('pbas_error') }}</div>
        @endif

        @yield('content')
    </div>

    @include('includes.footerJs')
    @stack('scripts')
    @include('includes.footer')
</div>
