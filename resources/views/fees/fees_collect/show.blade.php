@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Fees Collect</h4>
            </div>
        </div>

        <div class="card">
        @if(!empty($data['message']))
            @if(!empty($data['status_code']) && $data['status_code'] == 1)
                <div class="alert alert-success alert-block">
                    @else
                        <div class="alert alert-danger alert-block">
                            @endif
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>{{ $data['message'] }}</strong>
                        </div>
            @endif
        @php
        $grade_id = $standard_id = $division_id = '';
            if(isset($data['grade_id'])){
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
        @endphp
            <form action="{{ route('fees_collect.show_student') }}" enctype="multipart/form-data" method="post">
                {{ method_field("POST") }}
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="row">
                              {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 form-group">
                        <label>{{App\Helpers\get_string('studentname')}}<i class="mdi mdi-lead-pencil"></i></label>
                        <input type="text" id="stu_name" placeholder="{{App\Helpers\get_string('studentname')}}" name="stu_name" class="form-control" @if(isset($data['stu_name'])) value="{{$data['stu_name']}}" @endif>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{App\Helpers\get_string('uniqueid')}}<i class="mdi mdi-lead-pencil"></i></label>
                        <input type="text" id="uniqueid" placeholder="{{App\Helpers\get_string('uniqueid')}}" name="uniqueid" class="form-control" @if(isset($data['uniqueid'])) value="{{$data['uniqueid']}}" @endif>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Father Mobile</label>
                        <input type="text" id="mobile" placeholder="Father Mobile" name="mobile" class="form-control" @if(isset($data['mobile'])) value="{{$data['mobile']}}" @endif>
                    </div>                        
                    <div class="col-md-4 form-group">
                        <label>{{App\Helpers\get_string('grno')}}<i class="mdi mdi-lead-pencil"></i></label>
                        <input type="text" id="grno" placeholder="{{App\Helpers\get_string('grno')}}" name="grno" class="form-control" @if(isset($data['grno'])) value="{{$data['grno']}}" @endif>
                        @if(app('request')->input('implementation') == 1)
                        <input type="hidden" name="implementation" value="1">
                        @endif
                    </div>
                    <div class="col-md-4 form-group">
                        <div class="d-inline">
                            <input type="checkbox" name="including_inactive" value="Yes"
                                   @if(isset($data['including_inactive'])) @if($data['including_inactive'] == 'Yes') checked @endif @endif>
                            <span>In-active Students</span>
                        </div>
                    </div>
                    <div class="col-md-12 form-group">
                        <center>
                            <input type="submit" name="submit" value="Search Student" class="btn btn-success triz-btn" >
                        </center>
                    </div>
                </div>
            </form>

        </div>

       @if(isset($data['stu_data']))
            <div class="card">
                <span class="d-inline-block mb-2" tabindex="0" data-toggle="tooltip" title="Only those students will be displayed here whose Fees Structure is added.">
                  <button class="btn btn-danger" style="pointer-events: none;" type="button" disabled>Note</button>
                </span>
                <form action="{{ route('fees_collect.show_student') }}" enctype="multipart/form-data" method="post">
                    {{ method_field("POST") }}
                    @csrf
                    <div class="table-responsive">
                    <table id="example" class="table table-box table-bordered">
                    <thead>
                        <tr>
                            <th>Sr No.</th>
                            <th>{{ App\Helpers\get_string('studentname')}}</th>
                            <th>{{ App\Helpers\get_string('grno')}}</th>
                            <th>{{ App\Helpers\get_string('standard')}}</th>
                            <th>{{ App\Helpers\get_string('division')}}</a></th>                            
                            <th>{{ App\Helpers\get_string('studentquota')}}</th>
                            <th>Father Mobile</th>
                            <th>{{App\Helpers\get_string('uniqueid')}}</th>
                            <th>Remaining Fees</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data['stu_data'] as $id => $arr) 
                            <tr>
                                <td>{{$id + 1 }}</td>
                                <td>{{$arr->first_name . ' ' . $arr->middle_name . ' ' . $arr->last_name }}</td>
                                <td>{{$arr->enrollment_no }}</td>            
                                <td>{{$arr->standard_name }}</td>
                                <td>{{$arr->division_name }}</td>                                 
                                <td>{{$arr->stu_quota }}</td>                                 
                                <td>{{$arr->mobile }}</td>
                                <td>{{$arr->uniqueid }}</td>                                        
                                <td>{{$arr->bkoff }}</td>
                                <td>
                                    <a href="{{ route('fees_collect.edit',$arr->student_id)}}?std={{$arr->standard_id}}"><button style="float:left;" type="button" class="btn btn-info btn-outline ">Collect Fees</button></a>
                                </td>

                            </tr>
                      @endforeach
                      </tbody>

                    </table>
                </div>
                </form>
            </div>
        @endif
    </div>
</div>

@include('includes.footerJs')

@if (!isset($data['stu_data']))
@if(isset(Session::get('erpTour')['fees_collect']) && Session::get('erpTour')['fees_collect'] == 0)
<link rel="stylesheet" href="../../../tooltip/enjoyhint/jquery.enjoyhint.css">

        <script src="../../../tooltip/bower_components/todomvc-common/base.js"></script>
        <!-- <script src="../../../tooltip/bower_components/jquery/jquery.js"></script> -->
        <script src="../../../tooltip/bower_components/underscore/underscore.js"></script>
        <script src="../../../tooltip/bower_components/backbone/backbone.js"></script>
        <script src="../../../tooltip/bower_components/backbone.localStorage/backbone.localStorage.js"></script>
        <script src="../../../tooltip/js/models/todo.js"></script>
        <script src="../../../tooltip/js/collections/todos.js"></script>
        <script src="../../../tooltip/js/views/todo-view.js"></script>
        <script src="../../../tooltip/js/views/app-view.js"></script>
        <script src="../../../tooltip/js/routers/router.js"></script>
        <script src="../../../tooltip/js/app.js"></script>
        <script src="../../../tooltip/enjoyhint/enjoyhint.js"></script>
        <script src="../../../tooltip/enjoyhint/jquery.enjoyhint.js"></script>
        <script src="../../../tooltip/enjoyhint/kinetic.min.js"></script>
    <script>
      localStorage.clear();
      var enjoyhint_script_data = [
        {
            onBeforeStart: function(){
            $('#grade').change(function(e){

                enjoyhint_instance.trigger('new_todo');

            });
          },
          selector:'#grade',
          event:'new_todo',
          event_type:'custom',
          description:'Select Grade Here.'
        },
        {
            onBeforeStart: function(){
            $('#standard').change(function(e){

                enjoyhint_instance.trigger('new_todo');

            });
          },
          selector:'#standard',
          event:'new_todo',
          event_type:'custom',
          description:'Select Standard Here.'
        },
        {
          selector:'#division',
          event:'change',
          description:'Select Division Here.',
          timeout:100
        },
        {
          selector:'.btn-success',
          event:'click',
          description:'Please press to search students.',
          timeout:100
        }
      ];
      var enjoyhint_instance = null;
      $(document).ready(function(){
        enjoyhint_instance = new EnjoyHint({});
        enjoyhint_instance.setScript(enjoyhint_script_data);
        enjoyhint_instance.runScript();
      });
    </script>

    <script type="text/javascript">
        var url = "http://dev.triz.co.in/tourUpdate?module=fees_collect";
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
              console.log("success");
            }
        };
        xhttp.open("GET", url, true);
        xhttp.send();
    </script>
@endif
@endif

<script>
	 $(document).ready(function () {
        var table = $('#example').DataTable({
            select: true,
            lengthMenu: [
                [100, 500, 1000, -1],
                ['100', '500', '1000', 'Show All']
            ],
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    title: 'Fees Monthly Report',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    pageSize: 'A0',
                    exportOptions: {
                        columns: ':visible'
                    },
                },
                {extend: 'csv', text: ' CSV', title: 'Fees Monthly Report'},
                {extend: 'excel', text: ' EXCEL', title: 'Fees Monthly Report'},
                {extend: 'print', text: ' PRINT', title: 'Fees Monthly Report'},
                'pageLength'
            ],
        });

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

@if(app('request')->input('implementation') == 1)
<script type="text/javascript">
    document.body.className = document.body.className.replace("fix-header", "fix-header show-sidebar hide-sidebar");
    document.getElementById('main-header').style.display = 'none';
</script>
@endif
@include('includes.footer')
