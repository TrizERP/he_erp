{{--@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')--}}
@extends('layout')
@section('container')
<div id="page-wrapper">
    <div class="container-fluid">
            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">Question wise report</h4>
                </div>
            </div>
        @php
        $grade_id = $standard_id = $division_id = $order_by = $subject_id = '';

            if(isset($data['grade_id'])){
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if(isset($data['subject_id'])){
                $subject_id = $data['subject_id'];
            }
        @endphp
        <div class="card">
            <div class="card-body">
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
                            <form action="{{ route('show_question_wise_report') }}" enctype="multipart/form-data"
                                  method="post">
                                @csrf
                                <div class="row">
                                    {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}

                                    <div class="col-md-3 form-group">
                                <label for="subject">Select Subject</label>
                                <select name="subject" id="subject" class="cust-select form-control mb-0">
                                    @if(empty($data['subject_data']))
                                        <option value="">Select Subject</option>
                                    @endif

                                    @if(!empty($data['subject_data']))
                                        @foreach($data['subject_data'] as $k1 => $v1)
                                            <option
                                                value="{{$v1['subject_id']}}" @if(isset($data['subject_id'])){{$data['subject_id'] == $v1['subject_id'] ? 'selected=selected' : '' }} @endif>{{$v1['display_name']}} </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="exam">Select Exam</label>
                                <select class="cust-select form-control mb-0" name="exam"
                                        required="required">
                                    @if(!empty($data['exams_data']))
                                        @foreach($data['exams_data'] as $k => $v)
                                            <option
                                                value="{{$v->id}}" @if(isset($data->exam_id)){{$data->exam_id == $v->id ? 'selected=selected' : '' }} @endif>{{$v->paper_name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                                    {{-- <div class="col-md-4 form-group">
                                       <label>Order By</label>
                                       <select id='order_by' name="order_by" class="form-control">
                                           <option>Select Order By Field</option>
                                           <option @if($order_by == 'student_name') selected="selected" @endif value="student_name">{{App\Helpers\get_string('studentname','request')}}</option>
                                           <option @if($order_by == 'standard_id') selected="selected" @endif value="standard_id">{{App\Helpers\get_string('standard','request')}}</option>
                                           <option @if($order_by == 'enrollment_no') selected="selected" @endif value="enrollment_no">{{App\Helpers\get_string('grno','request')}}</option>
                                           <option @if($order_by == 'roll_no') selected="selected" @endif value="roll_no">Roll No</option>
                                       </select>
                                   </div> --}}
                                    <div class="col-md-12 col-sm-offset-4 text-center form-group">
                                        <input type="submit" name="submit" value="Search" class="btn btn-success">
                                        <button type="button" class="btn btn-info" data-toggle="modal"
                                                data-target="#exampleModal"><i class="mdi mdi-tune"></i></button>
                                    </div>
                                </div>
                                <!-- Modal -->
                                <div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1"
                                     role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Choose Field</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
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

                                    @if(isset($data['data']))
                                            @php
                                        //$checkedArray = array('enrollment_no','first_name','middle_name','last_name','mobile');
                                        $checkedArray = array();
                                        @endphp
                                        @foreach($data['data'] as $key => $value)
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
            </div>

            {{-- get question wise repost data --}}
        @if(isset($data['results']))
        @php $examResults = $data['results']; /* echo "<pre>"; print_r($data['results']); exit; */ @endphp
        @foreach ( $examResults as $examResultKey => $examResult )
            <div class="card">
                <div class="row">
                    <div class="col-md-3">
                        <strong>{{App\Helpers\get_string('standard','request')}} - {{ $data['standard_name'] }}</strong>
                    </div>
                    <div class="col-md-3">
                        <strong>{{App\Helpers\get_string('division','request')}} - {{ $data['division_name'] }}</strong>
                    </div>
                    <div class="col-md-3">
                        <strong>{{ $data['subject_name'] }}</strong>
                    </div>
                    <div class="col-md-3">
                        <strong>{{ session()->get('syear') }}</strong>
                    </div>
                    <div class="col-md-12 text-center py-2">
                        @php
                            $quation_paper = DB::table('question_paper')
                            ->select('paper_name')
                            ->where('id', $examResultKey)
                            ->first();

                            $quation_paper_name = ( $quation_paper ) ? $quation_paper->paper_name : '';

                            // echo "<pre>"; print_r($quation_paper); exit;
                        @endphp
                        <strong>{{ $quation_paper_name }}</strong>
                    </div>
                </div>
                @php
                    $colCountAns = [];
                    // $numberOfStudent = count($examResult);
                @endphp
                @foreach ( $examResult as $studentKey => $studentValue )
                    @foreach ($studentValue as $item)
                        @php
                                $countQuestion = count($item);
                        @endphp
                    @endforeach
                @endforeach
                    @php
                        // @foreach ($examResult as $student)
                    //     $stu_rollno = '';
                    //     $stu_name = '';
                    //     if ( $countQuestion > 0 ) {
                    //         $stu_rollno = $studentValue[0]->roll_no;
                    //         $stu_name = $studentValue[0]->student_name;

                    //     }


                        // echo "<pre>"; print_r($examResults); exit;
                    @endphp
                <div class="table-responsive mt-20 tz-report-table">
                    <table id="" class="table table-striped">
                        <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>{{App\Helpers\get_string('studentname','request')}}</th>
                            @for ($i = 1; $i <= $countQuestion; $i++)
                                            <th>Q{{ $i }}</th>
                                        @endfor
                            <th>Obtain</th>
                            <th>Total</th>
                            <th>Per(%)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $no_of_student = 0;
                        @endphp
                        @foreach ( $examResult as $studentKey1 => $studentValue1 )
                            {{-- @php
                            // echo "<pre>"; print_r($student); exit;
                                $row = 1;
                                $totalRightAns = 0;
                            @endphp --}}
                            @foreach ($studentValue1 as $keyNew => $student)


                                @php
                                    // echo "<pre>"; print_r($studentValue1); exit;
                                        $row = 1;
                                        $totalRightAns = 0;
                                        $no_of_student++;
                                @endphp

                                <tr>
                                    {{-- <td>{{ $stu_rollno }}</td>
                                    <td>{{ $stu_name }}</td> --}}
                                    <td>{{ $student[0]->roll_no }}</td>
                                    <td>{{ $student[0]->student_name }}</td>
                                    @foreach( $student as $key => $value )
                                    @php
                                            // echo "<pre>"; print_r($value); exit;
                                                $cell_bg_color = '';
                                        @endphp
                                        @php
                                            if ( $value->ans_status == 'right' ) {
                                                $colCountAns[$row][] = 1;
                                                $totalRightAns++;
                                                $ans = '1';
                                            } elseif($value->ans_status == 'wrong') {
                                                $colCountAns[$row][] = 0;
                                                $ans = '0';
                                            }
                                            else
                                            {
                                                //$colCountAns[$row][] = '';
                                                $ans = '-';
                                            }

                                        @endphp

                                        <td class="" style="{{ ($ans === '0' ) ? 'background-color:#FFC7CE' : '' }}">{{ $ans }}</td>
                                        @php $row++; @endphp
                                    @endforeach

                                                <td class="font-weight-bold" style="background-color: #ffe699;">{{ $totalRightAns }}</td>
                                                <td class="font-weight-bold" style="background-color: #ffe699;">{{ $countQuestion }}</td>

                                                @php
                                                    $percentage = number_format( ( $totalRightAns * 100 ) / $countQuestion, 2 ) . '%';
                                                @endphp
                                                <td class="font-weight-bold" style="background-color: #b4c6e7;">{{ $percentage }}</td>
                                            </tr>
                            @endforeach
                        @endforeach
                                    @php
                                        // array_pop($colCountAns);
                                    @endphp
                                    <tr>
                                        <td colspan="2" class="text-right font-weight-bold" style="background-color: #ffe699;">Question Wise Total</td>
                                        @php
                                            $calTrueAnsCount = 0;
                                            $numberOfStudent = 0;
                                        @endphp
                                        @foreach ( $colCountAns as $trueAnsCol )
                                            @php
                                                $numberOfStudent = count($trueAnsCol);
                                                $calTrueAnsCount += array_sum( $trueAnsCol );
                                            @endphp
                                            <td class="font-weight-bold" style="background-color: #ffe699;">{{ array_sum( $trueAnsCol ) }}</td>
                                        @endforeach
                                        <td class="font-weight-bold" style="background-color: #b4c6e7;">{{ $calTrueAnsCount }}</td>

                                        @php
                                            $total_marks = $numberOfStudent * $countQuestion;
                                            $fullPer = number_format( ( $calTrueAnsCount * 100 ) / $total_marks, 2 ) . '%';
                                        @endphp
                                        <td colspan="2" rowspan="2" class="text-center font-weight-bold" style="background-color: #a9d08e;">{{ $fullPer }}</td>
                                    </tr>

                                    <tr>
                                        <td colspan="2" class="text-right font-weight-bold" style="background-color: #ffe699;">Total Student</td>
                                        @for ($i = 1; $i <= $countQuestion; $i++)
                                            <td class="font-weight-bold" style="background-color: #ffe699;">{{ $numberOfStudent }}</td>
                                        @endfor
                                        <td class="font-weight-bold" style="background-color: #b4c6e7;">{{ $total_marks }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            {{-- @php
                                echo "<pre>"; print_r($colCountAns);
                            @endphp --}}
                            {{-- @php
                                die('one table complate');
                            @endphp --}}
                        </div>
                    {{-- @endforeach --}}
                </div>
            {{-- @endforeach --}}
        @endforeach
    </div>
    @endif
</div>

@include('includes.footerJs')
<script>
    var checked = false;
function checkedAll() {
    if (checked == false) {
        checked = true
    } else {
        checked = false
    }
    for (var i = 0; i < document.getElementsByName('dynamicFields[]').length; i++) {
        document.getElementsByName('dynamicFields[]')[i].checked = checked;
    }
}
</script>
    <script>
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
                    {extend: 'csv', text: ' CSV', title: 'Student Report'},
                    {extend: 'excel', text: ' EXCEL', title: 'Student Report'},
                    {extend: 'print', text: ' PRINT', title: 'Student Report'},
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
<script>
    $("#standard").change(function () {
        var std_id = $("#standard").val();
        var path = "{{ route('ajax_LMS_StandardwiseSubject') }}";
        $('#subject').find('option').remove().end().append('<option value="">Select Subject</option>').val('');
        $.ajax({
            url: path, data: 'std_id=' + std_id, success: function (result) {
                for (var i = 0; i < result.length; i++) {
                    $("#subject").append($("<option></option>").val(result[i]['subject_id']).html(result[i]['display_name']));
                }
            }
        });
    })

    $("#subject").change(function(){
        var std_id = $("#standard").val();
        var sub_id = $("#subject").val();
        var path = "{{ route('ajax_LMS_SubjectWiseExam') }}";
        $.ajax({
            url: path,
            data: 'std_id=' + std_id + '&sub_id=' + sub_id,
            success: function (result) {
                var e = $('select[name="exam"]');
                $(e).find('option').remove().end();
                for (var i = 0; i < result.length; i++) {
                    $(e).append($("<option></option>").val(result[i]['id']).html(result[i]['paper_name']));
                }
            }
        });
    })

    $(document).ready(function () {
        $('#grade').attr("required", true);
        $('#standard').attr("required", true);
        $('#subject').attr("required", true);
    });
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
                    title: 'Other Fees Report',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    pageSize: 'A0',
                    exportOptions: {
                        columns: ':visible'
                    },
                },
                {extend: 'csv', text: ' CSV', title: 'Other Fees Report'},
                {extend: 'excel', text: ' EXCEL', title: 'Other Fees Report'},
                {extend: 'print', text: ' PRINT', title: 'Other Fees Report'},
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

@include('includes.footer')
@endsection
