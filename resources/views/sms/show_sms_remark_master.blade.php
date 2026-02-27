{{-- @include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation') --}}
@extends('layout')

@section('container')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="card">

            @if(session('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ session('success') }}</strong>
            </div>
            @endif

            <div class="row">
                <div class="col-lg-3">
                    <a href="{{ route('sms_remark_master.create') }}"
                       class="btn btn-info add-new">
                        <i class="fa fa-plus"></i> Add New
                    </a>
                </div>

                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Remark Status</th>
                                    <th>Sort Order</th>
                                    <th class="text-align">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['data'] as $row)
                                <tr>
                                    <td>{{ $row->title }}</td>
                                    <td>{{ $row->remark_status }}</td>
                                    <td>{{ $row->sort_order }}</td>
                                    <td>
                                        <a href="{{ route('sms_remark_master.edit', $row->id) }}"
                                           class="btn btn-outline-success">
                                            <i class="ti-pencil-alt"></i>
                                        </a>

                                        <form action="{{ route('sms_remark_master.destroy', $row->id) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger"
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@include('includes.footerJs')
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js"></script>
<script>
$(document).ready(function () {
    $('#example').DataTable();
});
</script>
@include('includes.footer')
@endsection
