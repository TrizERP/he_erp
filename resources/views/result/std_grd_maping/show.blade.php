@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="card">
            @if(!empty($data['message']))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $data['message'] }}</strong>
            </div>
            @endif
            <div class="row">        
                <div class="col-lg-3 col-sm-3 col-xs-3">
                    <a href="{{ route('std_grd_maping.create') }}" class="btn btn-info add-new"><i class="fa fa-plus"></i> Add New</a>
                </div>
                <div class="col-lg-12 col-sm-12 col-xs-12">   
                    <div class="table-responsive">                        
                        <table id="example" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Grade Scale</th>
                                    <th>Academic Section</th>
                                    <th>{{App\Helpers\get_string('standard','request')}}</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['data'] as $key => $data1)
                                <tr> 
                                    <td>{{$data1['scale_name']}}</td>
                                    <td>{{$data1['grade_name']}}</td>
                                    <td>{{$data1['standard_name']}}</td>
                                    <td>
                                        <div class="d-inline">                                            
                                            <a href="{{ route('std_grd_maping.edit',$data1['id'])}}" class="btn btn-outline-success">
                                                <i class="ti-pencil-alt"></i>
                                            </a>
                                        </div>
                                        <form action="{{ route('std_grd_maping.destroy', $data1['id'])}}" method="post" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger" onclick="return confirmDelete();" type="submit">
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
                                                $('#example').DataTable({

                                                });
                                            });

</script>
@include('includes.footer')
