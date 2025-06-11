@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Item Tax Master</h4>
            </div>
        </div>
        <div class="card">
            @if(!empty($data['message']))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $data['message'] }}</strong>
            </div>
            @endif
            <div class="row">
                <div class="col-lg-3 col-sm-3 col-xs-3">
                    <a href="{{ route("add_inventory_item_tax_master.create") }}" class="btn btn-info add-new"><i class="fa fa-plus"></i> Add Item Tax</a>
                </div>
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="table-responsive">                        
                        <table id="example" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Title</th>
                                    <th>Amount Percentage(%)</th>
                                    <th>Description</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
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
                                    <td>{{$data->title}}</td>
                                    <td>{{$data->amount_percentage}}</td>
                                    <td>{{$data->description_1}}</td>
                                    <td>{{$data->sort_order}}</td>
                                    <td>{{$data->status}}</td> 
                                    <td>
                                        <div class="d-inline">                                            
                                            <a href="{{ route('add_inventory_item_tax_master.edit',$data->id)}}" class="btn btn-outline-success">
                                               <i class="ti-pencil-alt"></i>
                                            </a>
                                        </div>
                                        <form action="{{ route('add_inventory_item_tax_master.destroy', $data->id)}}" method="post" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                           <button type="submit" class="btn btn-outline-danger" onclick="return confirmDelete();">
                                                <i class="ti-trash"></i>
                                            </button>
                                        </form>
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
</div>

@include('includes.footerJs')
<script>
$(document).ready(function () {
    $('#example').DataTable({
    });
});
</script>
@include('includes.footer')
