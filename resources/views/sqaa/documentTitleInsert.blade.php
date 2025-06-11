@extends('layout')
@section('container')

<div id="page-wrapper">
	<div class="container-fluid">
		<div class="row bg-title">
			<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
				<h4 class="page-title">Document Master</h4>
			</div>
		</div>

	<div class="card">
			@if ($sessionData = Session::get('data')) @if($sessionData['status_code'] == 1)
		<div class="alert alert-success alert-block">
			@else
			<div class="alert alert-danger alert-block">
				@endif
				<button type="button" class="close" data-dismiss="alert">×</button>
				<strong>{{ $sessionData['message'] }}</strong>
			</div>
			@endif

		<form action="{{route('naac_doc_master.store')}}" method="Post">
			<div class="row">
				@csrf
				<div class="col-md-3 form-group">
					<label>Title</label>
					<input type="text" id="title" name="title" placeholder="Enter Title" class="form-control" required>
				</div>
			
				@if(!empty($data['get_tabs']))
				<div class="col-md-3 form-group">
				<label>Level Tabs</label>
					<select class="form-control" name="tabs_id" id ="tabs_id" onchange="getParent('#tabs_id','#lev_1');">
						<option value="0">Select Tabs</option>
						@foreach($data['get_tabs'] as $get_tabs)
						<option value="{{$get_tabs->id}}">{{$get_tabs->title}}</option>
						@endforeach
					</select>
				</div>
				@endif

				<div class="col-md-3 form-group" id ="lev_1_div">
				<label>Level 1</label>
					<select class="form-control" name="lev_1" id ="lev_1" onchange="getParent('#lev_1','#lev_2');">
					
					</select>
				</div>

				<div class="col-md-3 form-group"  id ="lev_2_div">
				<label>Level 2</label>
					<select class="form-control" name="lev_2" id ="lev_2">
						
					</select>
				</div>

			</div>
			<div class="col-md-3 form-group" id="save_button">
				<input type="submit" id="save_button" value="Save" class="btn btn-success">
			</div>
		</form>
	</div>


	</div>
</div>
@include('includes.footerJs')
<script>
 $(document).ready(function () {
	$('#lev_1_div').hide();
	$('#lev_2_div').hide();
})

	function getParent(level,id){
		var parent_id = $(level).val();
		
			$(id).empty();
			$(id+'_div').show();
			$.ajax({
				type: 'GET',
				url: '/get-level',
				data: {current_level:parent_id},
				success: function(data) {
					// Append the "Select Level" option
					$(id).append('<option value="0">Select Level</option>');
					var i = 1;
					if (Array.isArray(data)) {
						data.forEach(function(value) {
							var option = '<option value="' + value.id + '" data-new="' + (i++) + '" data-description="' + value.description +
								'" >' + value.title + '</option>';
							$(id).append(option); // Use sel_element as the selector
						});
					}
				},
				error: function(error) {
					console.log(error);
				}
			});
		}
	
</script>
@include('includes.footer')
@endsection