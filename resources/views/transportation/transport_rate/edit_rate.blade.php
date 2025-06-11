@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Add New Rate	</h4>
            </div>            
        </div>        
        <div class="card">
            @if(session::has('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ session('success')}}</strong>
            </div>
            @endif
         
            <div class="col-lg-12 col-sm-12 col-xs-12" style="overflow:auto;">
            	<!-- code  -->
            		<form action="{{ route('transport_rate.update',$data->id ) }}" enctype="multipart/form-data" method="POST">
                        {{ method_field("PUT") }}
                        {{csrf_field()}}
                       
                        <div class="row">
                        <div class="col-md-4 form-group">
                              <label>Distance From School</label>
                            <input type="text" id="distance_from_school" required="" name="distance_from_school" value="{{$data->distance_from_school}}" class="form-control">
                            <!--<input type="text" id='mobile' required name="to_time" value="" class="form-control">-->
                        </div>


						<div class="col-md-4 form-group">
                              <label>From Distance</label>
                            <input type="text" id="from_distance" required="" name="from_distance" value="{{$data->from_distance}}" class="form-control">
                            <!--<input type="text" id='mobile' required name="to_time" value="" class="form-control">-->
                        </div>

                        <div class="col-md-4 form-group">
                              <label>To Distance</label>
                            <input type="text" id="to_distance" required="" name="to_distance" value="{{$data->to_distance}}" class="form-control">
                            <!--<input type="text" id='mobile' required name="to_time" value="" class="form-control">-->
                        </div>

                        <div class="col-md-4 form-group">
                              <label>Rick Old</label>
                            <input type="text" id="rick_old" required="" name="rick_old" value="{{$data->rick_old}}" class="form-control">
                            <!--<input type="text" id='mobile' required name="to_time" value="" class="form-control">-->
                        </div>

						<div class="col-md-4 form-group">
                              <label>Rick New</label>
                            <input type="text" id="rick_new" required="" name="rick_new" value="{{$data->rick_new}}" class="form-control">
                            <!--<input type="text" id='mobile' required name="to_time" value="" class="form-control">-->
                        </div>

                        <div class="col-md-4 form-group">
                              <label>Van Old</label>
                            <input type="text" id="van_old" required="" name="van_old" value="{{$data->van_old}}" class="form-control">
                            <!--<input type="text" id='mobile' required name="to_time" value="" class="form-control">-->
                        </div>

						<div class="col-md-4 form-group">
                              <label>Van New</label>
                            <input type="text" id="van_new" required="" name="van_new" value="{{$data->van_new}}" class="form-control">
                            <!--<input type="text" id='mobile' required name="to_time" value="" class="form-control">-->
                        </div>

                        <div class="col-md-12 form-group">
                            <center>
                                <input type="submit" name="submit" value="Update" class="btn btn-success">
                            </center>
                        </div>
                        </div>

                    </form>
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