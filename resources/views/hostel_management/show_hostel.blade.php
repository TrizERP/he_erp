@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">Hostel</h4> </div>
            </div>      
        <div class="card">
            <div class="panel-body">
                @if(!empty($data['message']))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $data['message'] }}</strong>
                </div>
                @endif
                <div class="col-lg-3 col-sm-3 col-xs-3">
                    <a href="{{ route("add_hostel_master.create") }}" class="btn btn-info add-new"><i class="fa fa-plus"></i> Add Hostel</a>
                </div>
                <br><br><br>
                <div class="table-responsive">
                    <table id="example" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Hostel Name</th>
                                <th>Hostel Type</th>
                                <th>Description</th>
                                <th>Warden</th>
                                <th>Warden Contact</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @foreach($data['data'] as $key => $data)
                            <tr>    
                                <td>{{$data->code}}</td>
                                <td>{{$data->name}}</td>
                                <td>{{$data->hostel_type_id}}</td>
                                <td>{{$data->description}}</td>
                                <td>{{$data->warden}}</td>
                                <td>{{$data->warden_contact}}</td>
                                <td>
                                    <div class="d-inline">
                                        <a href="{{ route('add_hostel_master.edit',$data->id)}}" class="btn btn-info btn-outline"><i class="ti-pencil-alt"></i></a>
                                    </div>
                                    <form class="d-inline" action="{{ route('add_hostel_master.destroy', $data->id)}}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-info btn-outline-danger" type="submit"><i class="ti-trash"></i></button>
                                    </form>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>        
    </div>
</div>

@include('includes.footerJs')
<script src="{{ asset("/plugins/bower_components/datatables/datatables.min.js") }}"></script>
<script>
$(document).ready(function () {
    $('#example').DataTable();
});
</script>
@include('includes.footer')
