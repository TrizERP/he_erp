@include('includes.headcss') @include('includes.header') @include('includes.sideNavigation')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
<link href="{{ asset('/plugins/bower_components/summernote/dist/summernote.css') }}" rel="stylesheet" />

<style>
.list-group-item{
    background-color : transparent !important;
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
            @if ($sessionData = Session::get('data'))
                    @if($sessionData['status_code'] == 1)
                    <div class="alert alert-success alert-block">
                    @else
                    <div class="alert alert-danger alert-block">
                    @endif
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>{{ $sessionData['message'] }}</strong>
                    </div>
                    @endif
				<form action="{{ route('naac_report_master.create') }}" enctype="multipart/form-data" class="row">
					<div class="col-md-4 form-group" id="level_1_div">
						<label>Select Level 1</label>
                        <select name="level_1" id="level_1" class="form-control">
                        <option value=0>--Select Level 1--</option>                        
                        @foreach($data['level_1'] as $key => $value)
                        <option value="{{$value['id']}}" @if(isset($data['selected_1']) && $data['selected_1']==$value['id']) selected @endif>{{$value['title']}}</option>
                        @endforeach
                        </select>
					</div>
                    @if(isset($data['selected_1']))
                    <div class="col-md-4 form-group" id="level_2_div">
						<label>Select Level 2</label>
                        <select name="level_2_sel" id="level_2_sel" class="form-control">
                        <option value=0>--Select Level 2--</option>                        
                        @foreach($data['level_2_val'] as $key => $value)
                        <option value="{{$value['id']}}" @if(isset($data['selected_2']) && $data['selected_2']==$value['id']) selected @endif>{{$value['title']}}</option>
                        @endforeach
                        </select>
					</div>
                    @endif

                    @if(isset($data['selected_2']))
                    <div class="col-md-4 form-group" id="level_3_div">
						<label>Select Level 3</label>
                        <select name="level_3_sel" id="level_3_sel" class="form-control">
                        <option value=0>--Select Level 3--</option>                        
                        @foreach($data['level_3_val'] as $key => $value)
                        <option value="{{$value['id']}}" @if(isset($data['selected_3']) && $data['selected_3']==$value['id']) selected @endif>{{$value['title']}}</option>
                        @endforeach
                        </select>
					</div>
                    @endif

                    @if(isset($data['selected_3']))
                    <div class="col-md-4 form-group" id="level_4_div">
						<label>Select Level 4</label>
                        <select name="level_4_sel" id="level_4_sel" class="form-control">
                        <option value=0>--Select Level 4--</option>                        
                        @foreach($data['level_4_val'] as $key => $value)
                        <option value="{{$value['id']}}" @if(isset($data['selected_4']) && $data['selected_4']==$value['id']) selected @endif>{{$value['title']}}</option>
                        @endforeach
                        </select>
					</div>
                    @endif
					<div class="col-md-12 form-group">
						<center>
							<input type="submit" name="submit" value="Search" class="btn btn-success">
						</center>
					</div>

				</form>
			</div>
      
		@if(isset($data['data']) && !empty($data['data']))
		<div class="card">
			<div class="row">
                 <form action="{{route('naac_detail_update.update')}}" method="post">
                    @csrf
                            <input type="hidden" name="menu_id" value="{{$data['menu_id']}}">
                            <!-- editor  -->
                            <textarea name="hidden_input" cols="2" rows="2" id="hidden_input" style="display:none"></textarea>
                            <label>Details</label>
                        <textarea class="summernote" id="naa_details" name="naa_details" required>
                                  @isset($data['naac_details'][0]->details){!! $data['naac_details'][0]->details !!}@endisset
                            </textarea> 
                               
                         <div class="col-md-4">
                             <input type="submit" name="save" value="Update" class="btn btn-success">
                         </div>
                     </form>
                 
				<div class="col-lg-12 col-sm-12 col-xs-12">
					<div class="table-responsive">
						<table id="example" class="table table-striped">
							<thead>
								<tr>
									<th>SR NO</th>
									<th>Document</th>
									<th>Availability</th>
									<th>File</th>
									<th>Marks</th>  
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
                            @php 
                            $i = 1;
                            @endphp 
								@foreach ($data['data'] as $key=>$item)
								<tr>
									<td>{{ $i++ }}</td>
									<td>{!! $item['document_title'] !!}</td>
									<td>{{ $item['availability'] ?? 'NO' }}</td>
									<td>
										@if (!empty($item['file']))
										<a href="https://s3-triz.fra1.digitaloceanspaces.com/public/sqaa/{{$item['file']}}" target="_blank">View</a>
										@else N/A @endif
									</td>
									<td>{{ $item['mark'] }}</td>   
									<td>
                                    <div class="d-flex align-items-center justify-content-end">
                                    <a href="{{ route('naac_report_master.edit', ['id' => $item['id'] ?? 0, 'document_id' => $item['document_id'] ?? 0]) }}" class="btn btn-outline-success mr-1"><i class="ti-pencil-alt"></i></a>

                                    <form action="{{ route('naac_report_master.destroy', $item['id'] )}}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="document_id" id="document_id" value="{{ $item['document_id'] }}">
                                        <button type="submit" onclick="return confirmDelete();" class="btn btn-info btn-outline-danger"><i class="ti-trash"></i></button>
                                    </form>
                                    </div>
                                    </td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		@endif
	</div>
</div>

@include('includes.footerJs')
<script src="{{asset('/plugins/bower_components/summernote/dist/summernote.min.js')}}"></script>
    <script>
            $(document).ready(function() {
                @if(isset($data['selected_1']))
                var selectedTabId = "section-linemove-{{ $data['selected_1'] }}";
                $('#' + selectedTabId).addClass('show active');
                $('a[href="#' + selectedTabId + '"]').tab('show');
                @endif

                $(document).ready(function() {
                    window.
                    $('.summernote').summernote({
                        height: 'auto', // Set the height to 'auto'
                        minHeight: null,
                        maxHeight: null,
                        focus: false
                    });

                    $('[data-toggle="popover"]').popover({
                        title: "",
                        html: true
                    });

                    $('[data-toggle="popover"]').on('click', function(e) {
                        $('[data-toggle="popover"]').not(this).popover('hide');

                    });
                });

            });
        </script>
<script>

$(document).on('change','#level_1',function(){
    var level_1 = $(this).val();
    // Clear existing level_2 options
    $('#level_2_div').remove();    
    $('#level_3_div').remove();        
    $('#level_4_div').remove();    
    $.ajax({
        type: 'GET',
        url: '/get-level', 
        data: { level_2: level_1 }, 
        success: function (data) {
        var level_2_select_container = $('#level_2_div');
        var level_2_select = $('#level_2_sel');

            if (Array.isArray(data) && data.length > 0) {
                if (level_2_select_container.length === 0) {
                    level_2_select_container = $('<div class="col-md-4 form-group" id="level_2_div"></div>');
                    $('#level_1_div').after(level_2_select_container);
                    var level_2_select_label = $('<label for="level_2_sel">Select Level 2</label>');
                    level_2_select = $('<select id="level_2_sel" class="form-control" name="level_2_sel"></select>');
                    var defaultOption = '<option value="0">--Select Level 2--</option>';
                    level_2_select.append(defaultOption);

                    level_2_select_container.append(level_2_select_label);
                    level_2_select_container.append(level_2_select);
                }

                // Populate the level_2 options
                data.forEach(function (value) {
                    var option = '<option value="' + value.id + '">' + value.title + '</option>';
                    level_2_select.append(option);
                });
            }
        }
    });
});


$(document).on('change','#level_2_sel',function(){
    var level_2 = $(this).val();
    $('#level_3_div').remove();        
    $('#level_4_div').remove();    
   
    $.ajax({
        type: 'GET',
        url: '/get-level', 
        data: { level_3: level_2 }, 
        success: function (data) {
        var level_3_select_container = $('#level_3_div');
        var level_3_select = $('#level_3_sel');

            if (Array.isArray(data) && data.length > 0) {
                if (level_3_select_container.length === 0) {
                    level_3_select_container = $('<div class="col-md-4 form-group" id="level_3_div"></div>');
                    $('#level_2_div').after(level_3_select_container);
                    var level_3_select_label = $('<label for="level_3_sel">Select Level 3</label>');
                    level_3_select = $('<select id="level_3_sel" class="form-control" name="level_3_sel"></select>');
                    var defaultOption = '<option value="0">--Select Level 3--</option>';
                    level_3_select.append(defaultOption);

                    level_3_select_container.append(level_3_select_label);
                    level_3_select_container.append(level_3_select);
                }

                // Populate the level_3 options
                data.forEach(function (value) {
                    var option = '<option value="' + value.id + '">' + value.title + '</option>';
                    level_3_select.append(option);
                });
            }
        }
    });

});


$(document).on('change','#level_3_sel',function(){
    var level_3 = $(this).val();
    $('#level_4_div').remove();    
    
    $.ajax({
        type: 'GET',
        url: '/get-level', 
        data: { level_4: level_3 }, 
        success: function (data) {
        var level_4_select_container = $('#level_4_div');
        var level_4_select = $('#level_4_sel');

            if (Array.isArray(data) && data.length > 0) {
                if (level_4_select_container.length === 0) {
                    level_4_select_container = $('<div class="col-md-4 form-group" id="level_4_div"></div>');
                    $('#level_3_div').after(level_4_select_container);
                    var level_4_select_label = $('<label for="level_4_sel">Select Level 4</label>');
                    level_4_select = $('<select id="level_4_sel" class="form-control" name="level_4_sel"></select>');
                    var defaultOption = '<option value="0">--Select Level 4--</option>';
                    level_4_select.append(defaultOption);

                    level_4_select_container.append(level_4_select_label);
                    level_4_select_container.append(level_4_select);
                }

                // Populate the level_2 options
                data.forEach(function (value) {
                    var option = '<option value="' + value.id + '">' + value.title + '</option>';
                    level_4_select.append(option);
                });
            }
        }
    });

});

 $(document).ready(function () {
            var table = $('#example').DataTable({
                ordering: false,
                select: true,
                lengthMenu: [
                    [100, 500, 1000, -1],
                    ['100', '500', '1000', 'Show All']
                ],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        title: 'Student Report',
                        orientation: 'landscape',
                        pageSize: 'LEGAL',
                        pageSize: 'A0',
                        exportOptions: {
                            columns: ':visible'
                        },
                    },
                    {extend: 'csv', text: ' CSV', title: 'NACC Report'},
                    {extend: 'excel', text: ' EXCEL', title: 'NACC Report'},
                    {extend: 'print', text: ' PRINT', title: 'NACC Report'},
                    'pageLength'
                ],
            });
            //table.buttons().container().appendTo('#example_wrapper .col-md-6:eq(0)');

            $('#example thead tr').clone(true).appendTo('#example thead');
            $('#example thead tr:eq(1) th').each(function (i) {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Search ' + title + '" />');

                $('input', this).on('keyup change', function () {
                    if (table.column(i).search() !== this.value) {
                        table
                        .column(i)
                        .search( this.value )
                        .draw();
                }
            } );
        } );
    } );
</script>
@include('includes.footer')