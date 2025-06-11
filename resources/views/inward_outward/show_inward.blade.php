@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">Inward</h4> 
                </div>
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
                        <a href="{{ route('add_inward.create') }}" class="btn btn-info add-new"><i class="fa fa-plus"></i> Add Inward</a>
                    </div>
                    
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <div class="table-responsive">
                            <table id="example" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Sr.No.</th>
                                        <th>From Place</th>
                                        <th>Inward No.</th>
                                        <th>Subject</th>
                                        <th>Description</th>
                                        <th>File Name</th>
                                        <th>File Location</th>
                                        <th>Academic Year</th>
                                        <th>Inward Date</th>
                                        <th>Attachment</th>                                    
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php
                                $j=1;
                                @endphp
                                    @foreach($data['data'] as $key => $data)
                                    <tr> 
                                        <td>{{$j}}</td>
                                        <td>{{$data->place_id}}</td>
                                        <td>{{$data->inward_number}}</td>
                                        <td>{{$data->title}}</td>  
                                        <td>{{$data->description}}</td> 
                                        <td>{{$data->file_name}}</td> 
                                        <td>{{$data->file_location_id}}</td> 
                                        <td>{{$data->acedemic_year}}</td> 
                                        <td>{{date('d-m-Y',strtotime($data->inward_date))}}</td> 
                                        <td><a target="blank" href="/storage/inward/{{$data->attachment}}">{{$data->attachment}}</a> </td>                                     
                                        <td>                                        
                                            <div class="d-flex align-items-center justify-content-end">
                                                <a href="{{ route('add_inward.edit',$data->id)}}" class="btn btn-outline-success mr-1">
                                                    <i class="ti-pencil-alt"></i></a>
                                                <form action="{{ route('add_inward.destroy', $data->id)}}" class="d-inline" method="post">
                                                @csrf
                                                @method('DELETE')
                                                    <button onclick="return confirmDelete();" type="submit" class="btn btn-outline-danger"><i class="ti-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>     
                                    </tr>
                                    @php
                                    $j++;
                                    @endphp
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

<script src="{{ asset("/plugins/bower_components/datatables/datatables.min.js") }}"></script>
<script>
$(document).ready(function () {
    $('#example').DataTable();
});

</script>
@include('includes.footer')
