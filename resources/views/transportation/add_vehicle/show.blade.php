@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">            
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">                
                <h4 class="page-title">Add Vehicle</h4>            
            </div>                    
        </div>        
            <div class="card">
     
                @if(!empty($data['message']))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $data['message'] }}</strong>
                </div>
                @endif
                <div class="col-lg-3 col-sm-3 col-xs-3">
                    <a href="{{ route('add_vehicle.create') }}" class="btn btn-info add-new"><i class="fa fa-plus"></i> Add New</a>
                </div>
                <br><br><br>
                <div class="col-lg-12 col-sm-12 col-xs-12" style="overflow:auto;">
                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Vehicle Number</th>
                                <th>Vehicle Type</th>
                                <th>Sitting Capacity</th>
                                <th>Institute Shift</th>
                                <th>Vehicle Identity Number</th>
                                <th>Driver</th>
                                <th>Conductor</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['data'] as $key => $data)

                            <tr>    
                                <td>{{$data->title}}</td>
                                <td>{{$data->vehicle_number}}</td>
                                <td>{{$data->vehicle_type}}</td>
                                <td>{{$data->sitting_capacity}}</td>
                                <td>{{$data->shift_title}}</td>
                                <td>{{$data->vehicle_identity_number}}</td>
                                <td>{{$data->first_name}}</td>
                                <td>{{$data->cond}}</td>

                                <td>
                                    <div class="d-inline">
                                    <a href="{{ route('add_vehicle.edit',$data->id)}}" class="btn btn-info btn-outline">
                                        <i class="ti-pencil-alt"></i>
                                    </a>
                                    </div>
                                    <form class="d-inline" action="{{ route('add_vehicle.destroy', $data->id)}}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-info btn-outline-danger" onclick="return confirmDelete();" type="submit"><i class="ti-trash"></i></button>
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

@include('includes.footerJs')
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js"></script>
<script>
$(document).ready(function () {
    $('#example').DataTable({
        
    });
});

</script>
@include('includes.footer')
