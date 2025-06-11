@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<style >
    .filter-button 
    {
        margin: 0 !important;
    }
</style>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Admission Registration Report</h4>
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
            <form action="{{ route('admission_registration_report') }}" enctype="multipart/form-data" method="post">
                {{ method_field("POST") }}
                @csrf
                <div class="row">                    
                    <div class="col-md-4 form-group">
                        <label>From Date </label>
                        <input type="text" id='from_date' autocomplete="off" required name="from_date" @if(isset($data['from_date'])) value="{{$data['from_date']}}" @endif class="form-control mydatepicker">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>To Date </label>
                        <input type="text" id='to_date' autocomplete="off" required name="to_date" @if(isset($data['to_date'])) value="{{$data['to_date']}}" @endif class="form-control mydatepicker">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Status </label>
                        <select id='status' name="status" class="form-control">
                            <option value=""> Select Status </option>
                            <option value="OPEN" @if(isset($data['status'])) @if($data['status'] == "OPEN") selected="selected" @endif @endif> Open </option>
                            <option value="CLOSE" @if(isset($data['status'])) @if($data['status'] == "CLOSE") selected="selected" @endif @endif> Close </option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <center>                            
                            <input type="submit" name="report" value="Search" class="btn btn-success" > 
                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModal"><i class="mdi mdi-tune"></i></button>
                        </center>
                    </div>

                    <div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Choose Field</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">x</span>
                            </button>
                          </div>
                          <div class="modal-body">
                                <div class="slimscrollright">
                                    <div class="rpanel-title"><span><i class="ti-close right-side-toggle"></i></span> </div>
                                    <div class="row">
                                        <div class="col-md-12 form-group mb-2">
                                            <div class="checkbox checkbox-info">
                                                <input id="checkall" onclick="checkedAll();" name="checkall" type="checkbox">
                                                <label for="checkall"> Check All </label>
                                                <input type="hidden" name="page" value="bulk">
                                            </div>
                                        </div>
                            
                                    @if(isset($data['fields']))
                                        @php                                                                            
                                        $checkedArray = array();
                                        @endphp
                                        @foreach($data['fields'] as $key => $value)
                                        <div class="col-md-4 form-group mt-1">
                                            <div class="custom-control custom-checkbox">
                                                @php
                                                $checked = '';
                                                if(in_array($key,$checkedArray)){
                                                    $checked = 'checked="checked"';
                                                }
                                                if(isset($data['headers'])){
                                                    if(count($data['headers']) > 0){
                                                        $headersChecked = array_keys($data['headers']);
                                                    }
                                                    $checked = '';
                                                    if(in_array($key,$headersChecked)){
                                                        $checked = 'checked="checked"';
                                                    }
                                                }
                                                @endphp
                                                <input id="{{$key}}" {{$checked}} value="{{$key}}" class="custom-control-input" name="dynamicFields[]" type="checkbox">
                                                <label for="{{$key}}" class="custom-control-label"> {{$value}} </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
            </form> 
        </div>       
                    
        @if(isset($data['data']))
        <div class="card">
            <div class="row">                
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        {!! App\Helpers\get_school_details("","","") !!}
                        <table id="example" class="table table-striped">
                            <thead>
                                <tr>
                                    @foreach($data['headers'] as $hkey => $header)
                                        <th> {{ucfirst(str_replace("_", " ", $header))}} </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                            	@foreach($data['data'] as $key => $value)
                                    <tr>    
                                        @foreach($data['headers'] as $hkey => $header)
                                        @if($header == 'followup_date' || $header == 'created_on' || $header == 'date_of_birth')
                                            <td> {{date('d-m-Y H:i:s', strtotime($value[$header]))}} </td> 
                                        @else
                                            <td> {{$value[$header]}} </td>
                                        @endif
                                        @endforeach 
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

<script>
    var checked = false;
function checkedAll()
{
    if (checked == false) {
        checked = true
    } else {
        checked = false
    }
    for (var i = 0; i < document.getElementsByName('dynamicFields[]').length; i++)
    {
        document.getElementsByName('dynamicFields[]')[i].checked = checked;
    }
}    
</script>

<script>
    $(document).ready(function() {
     var table = $('#example').DataTable( {
         select: true,          
         lengthMenu: [ 
                        [100, 500, 1000, -1], 
                        ['100', '500', '1000', 'Show All'] 
        ],
        dom: 'Bfrtip', 
        buttons: [ 
            { 
                extend: 'pdfHtml5',
                title: 'Admission Form Report',
                orientation: 'landscape',
                pageSize: 'LEGAL',                
                pageSize: 'A0',
                exportOptions: {                   
                     columns: ':visible'                             
                },
            }, 
            { extend: 'csv', text: ' CSV', title: 'Admission Form Report' }, 
            { extend: 'excel', text: ' EXCEL', title: 'Admission Form Report'}, 
            {
                extend: 'print',
                text: ' PRINT',
                title: 'Admission Form Report',
                customize: function (win) {
                    $(win.document.body).prepend(`{!! App\Helpers\get_school_details("", "", "") !!}`);
                }
            },
            'pageLength' 
        ], 
        }); 

        $('#example thead tr').clone(true).appendTo( '#example thead' );
        $('#example thead tr:eq(1) th').each( function (i) {
            var title = $(this).text();
            $(this).html( '<input type="text" placeholder="Search '+title+'" />' );

            $( 'input', this ).on( 'keyup change', function () {
                if ( table.column(i).search() !== this.value ) {
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
