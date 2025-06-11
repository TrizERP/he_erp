@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">       
            <div class="card">
                <div class="panel-body">
                    @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-block">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>{{ $message }}</strong>
                    </div>
                    @endif
                       
                    <div class="col-lg-12 col-sm-12 col-xs-12">  
                        <form action="
                          @if (isset($data))
                          {{ route('add_hostel_type_master.update', $data->id) }}
                          @else
                          {{ route('add_hostel_type_master.store') }}
                          @endif

                          " method="post">

                        @if(!isset($data))
                        {{ method_field("POST") }}
                        @else
                        {{ method_field("PUT") }}
                        @endif


                        {{csrf_field()}}
                        
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    @csrf
                                    <label>Hostel Type </label>
                                    <input type="text" id='hostel_type' required name="hostel_type" value="@if(isset($data->hostel_type)){{$data->hostel_type}}@endif" class="form-control">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Description </label>
                                    <textarea class="form-control" id='description' required name="description">@if(isset($data->description)){{$data->description}}@endif</textarea>  
                                </div>

                                <div class="col-md-4 form-group">                           
                                    <label>Status</label>                                
                                    <div class="radio radio-info">
                                        <input type="radio" name="status" id="yes" value="Yes" @if(isset($data->status)) {{ $data->status == 'Yes' ? 'checked' : '' }} @endif> 
                                        <label for="yes"> Yes </label>
                                      
                                        <input type="radio" id="no" name="status" value="No" @if(isset($data->status)){{ $data->status == 'No' ? 'checked' : '' }} @endif> 
                                            <label for="no"> No </label>
                                    </div>
                                </div>                            

                                <div class="col-md-2 form-group">
                                    <input type="submit" name="submit" value="Save" class="btn btn-success" >
                                </div>
                            </div>

                        </form>
                    </div>

                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>            
    </div>
</div>

@include('includes.footerJs')
@include('includes.footer')
