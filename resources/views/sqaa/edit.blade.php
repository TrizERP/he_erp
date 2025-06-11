@include('includes.headcss') @include('includes.header') @include('includes.sideNavigation')
<style>
	.list-group-item {
		background-color: transparent !important;
	}
</style>
<div id="page-wrapper">
	<div class="container-fluid">
		<div class="row bg-title">
			<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
				<h4 class="page-title">Master Report</h4>
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
				<form action="{{ route('naac_report_master.update', $data['data']->id) }}" method="POST" enctype="multipart/form-data" class="row">
                @csrf
                @method('PUT')
					<div class="col-md-4 form-group" id="level_1_div">
						<label>Select Level 1</label>
						<select name="level_1" id="level_1" class="form-control">
							<option>--Select Level 1--</option>
							@foreach($data['level_1'] as $key => $value)
							<option value="{{$value['id']}}" @if(isset($data[ 'sel_1']) && $data[ 'sel_1']==$value[ 'id']) selected @endif>{{$value['title']}}</option>
							@endforeach
						</select>
					</div>
					@if(isset($data['sel_2']))
					<div class="col-md-4 form-group" id="level_2_div">
						<label>Select Level 2</label>
						<select name="level_2_sel" id="level_2_sel" class="form-control">
							<option>--Select Level 2--</option>
							@foreach($data['level_2_val'] as $key => $value)
							<option value="{{$value['id']}}" @if(isset($data[ 'sel_2']) && $data[ 'sel_2']==$value[ 'id']) selected @endif>{{$value['title']}}</option>
							@endforeach
						</select>
					</div>
					@endif @if(isset($data['sel_3']))
					<div class="col-md-4 form-group" id="level_3_div">
						<label>Select Level 3</label>
						<select name="level_3_sel" id="level_3_sel" class="form-control">
							<option>--Select Level 3--</option>
							@foreach($data['level_3_val'] as $key => $value)
							<option value="{{$value['id']}}" @if(isset($data[ 'sel_3']) && $data[ 'sel_3']==$value[ 'id']) selected @endif>{{$value['title']}}</option>
							@endforeach
						</select>
					</div>
					@endif @if(isset($data['sel_4']))
					<div class="col-md-4 form-group" id="level_4_div">
						<label>Select Level 4</label>
						<select name="level_4_sel" id="level_4_sel" class="form-control">
							<option>--Select Level 4--</option>
							@foreach($data['level_4_val'] as $key => $value)
							<option value="{{$value['id']}}" @if(isset($data[ 'sel_4']) && $data[ 'sel_4']==$value[ 'id']) selected @endif>{{$value['title']}}</option>
							@endforeach
						</select>
					</div>
					@endif
                  	<div class="col-md-4">
						<div class="form-group">
							<label for="topicAvailability">Document</label>
							<textarea name="document" id="document" rows="3" class="form-control" data-new="1" onchange="chat_gtp(this.value,1)">{!! $data['data']->document_title !!}</textarea>
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label for="topicAvailability2">Availability</label>
							<select class="form-control map-value mb-0" name="availability" data-new="1" onchange="toggleInput(1)" id="selectavail"
							 required="">
								<option value="">Select Availability</option>
								<option value="yes" @if($data['data']->availability == "yes") selected @endif>Yes</option>
								<option value="no" @if($data['data']->availability == "no") selected @endif>No</option>
								<option value="inprocess" @if($data['data']->availability == "inprocess") selected @endif>In-Process</option>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="topicAvailability2">Files</label>
							<input type="file" class="form-control" name="files" data-new="1" accept=".pdf,.xlsx,.doc,.docx" id="fileInput"> @if (!empty($data['data']->file))
							<a href="https://s3-triz.fra1.digitaloceanspaces.com/public/sqaa/{{$data['data']->file}}" target="_blank">View</a>
							@else N/A @endif
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label for="topicAvailability">Marks</label>
							<input type="text" name="marks" id="marks"  value="{{$data['data']->mark}}" readonly>
							<input type="hidden" name="document_id" id="document_id"  value="{{$data['data']->document_id}}">                            
						</div>
					</div>

					<div class="col-md-12 form-group">
						<center>
							<input type="submit" name="submit" value="Update" class="btn btn-success">
						</center>
					</div>

				</form>
			</div>

		</div>
	</div>

	@include('includes.footerJs')
	<script>
		$(document).on('change', '#level_1', function() {
			var level_1 = $(this).val();
			// Clear existing level_2 options
			$('#level_2_div').remove();
			$('#level_3_div').remove();
			$('#level_4_div').remove();
			$.ajax({
				type: 'GET',
				url: '/get-level',
				data: {
					level_2: level_1
				},
				success: function(data) {
					var level_2_select_container = $('#level_2_div');
					var level_2_select = $('#level_2_sel');

					if (Array.isArray(data) && data.length > 0) {
						if (level_2_select_container.length === 0) {
							level_2_select_container = $('<div class="col-md-4 form-group" id="level_2_div"></div>');
							$('#level_1_div').after(level_2_select_container);
							var level_2_select_label = $('<label for="level_2_sel">Select Level 2</label>');
							level_2_select = $('<select id="level_2_sel" class="form-control" name="level_2_sel"></select>');
							var defaultOption = '<option value="">--Select Level 2--</option>';
							level_2_select.append(defaultOption);

							level_2_select_container.append(level_2_select_label);
							level_2_select_container.append(level_2_select);
						}

						// Populate the level_2 options
						data.forEach(function(value) {
							var option = '<option value="' + value.id + '">' + value.title + '</option>';
							level_2_select.append(option);
						});
					}
				}
			});
		});


		$(document).on('change', '#level_2_sel', function() {
			var level_2 = $(this).val();
			$('#level_3_div').remove();
			$('#level_4_div').remove();

			$.ajax({
				type: 'GET',
				url: '/get-level',
				data: {
					level_3: level_2
				},
				success: function(data) {
					var level_3_select_container = $('#level_3_div');
					var level_3_select = $('#level_3_sel');

					if (Array.isArray(data) && data.length > 0) {
						if (level_3_select_container.length === 0) {
							level_3_select_container = $('<div class="col-md-4 form-group" id="level_3_div"></div>');
							$('#level_2_div').after(level_3_select_container);
							var level_3_select_label = $('<label for="level_3_sel">Select Level 3</label>');
							level_3_select = $('<select id="level_3_sel" class="form-control" name="level_3_sel"></select>');
							var defaultOption = '<option value="">--Select Level 3--</option>';
							level_3_select.append(defaultOption);

							level_3_select_container.append(level_3_select_label);
							level_3_select_container.append(level_3_select);
						}

						// Populate the level_3 options
						data.forEach(function(value) {
							var option = '<option value="' + value.id + '">' + value.title + '</option>';
							level_3_select.append(option);
						});
					}
				}
			});

		});


		$(document).on('change', '#level_3_sel', function() {
			var level_3 = $(this).val();
			$('#level_4_div').remove();

			$.ajax({
				type: 'GET',
				url: '/get-level',
				data: {
					level_4: level_3
				},
				success: function(data) {
					var level_4_select_container = $('#level_4_div');
					var level_4_select = $('#level_4_sel');

					if (Array.isArray(data) && data.length > 0) {
						if (level_4_select_container.length === 0) {
							level_4_select_container = $('<div class="col-md-4 form-group" id="level_4_div"></div>');
							$('#level_3_div').after(level_4_select_container);
							var level_4_select_label = $('<label for="level_4_sel">Select Level 4</label>');
							level_4_select = $('<select id="level_4_sel" class="form-control" name="level_4_sel"></select>');
							var defaultOption = '<option value="">--Select Level 4--</option>';
							level_4_select.append(defaultOption);

							level_4_select_container.append(level_4_select_label);
							level_4_select_container.append(level_4_select);
						}

						// Populate the level_2 options
						data.forEach(function(value) {
							var option = '<option value="' + value.id + '">' + value.title + '</option>';
							level_4_select.append(option);
						});
					}
				}
			});

		});

		$(document).ready(function() {
			var table = $('#example').DataTable({
				ordering: false,
				select: true,
				lengthMenu: [
					[100, 500, 1000, -1],
					['100', '500', '1000', 'Show All']
				],
				dom: 'Bfrtip',
				buttons: [{
						extend: 'pdfHtml5',
						title: 'Student Report',
						orientation: 'landscape',
						pageSize: 'LEGAL',
						pageSize: 'A0',
						exportOptions: {
							columns: ':visible'
						},
					},
					{
						extend: 'csv',
						text: ' CSV',
						title: 'Student Report'
					},
					{
						extend: 'excel',
						text: ' EXCEL',
						title: 'Student Report'
					},
					{
						extend: 'print',
						text: ' PRINT',
						title: 'Student Report'
					},
					'pageLength'
				],
			});
			//table.buttons().container().appendTo('#example_wrapper .col-md-6:eq(0)');

			$('#example thead tr').clone(true).appendTo('#example thead');
			$('#example thead tr:eq(1) th').each(function(i) {
				var title = $(this).text();
				$(this).html('<input type="text" placeholder="Search ' + title + '" />');

				$('input', this).on('keyup change', function() {
					if (table.column(i).search() !== this.value) {
						table
							.column(i)
							.search(this.value)
							.draw();
					}
				});
			});
		});
	</script>
	@include('includes.footer')