@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">View Facultywise Timetable</h4>
            </div>
        </div>
        <div class="card">
            @if ($message = Session::get('data'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message['message'] }}</strong>
            </div>
            @endif
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <form action="{{ route('facultywise_timetable.create') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            @php
                                $dep_id = '';
                                if (isset($data['department_id'])) {
                                    $dep_id = $data['department_id'];
                                }
                            @endphp

                            {!! App\Helpers\HrmsDepartments("", "", $dep_id, "", "", "") !!}
                            
                            <!-- Hidden field to convert emp_id to teacher_id -->
                            <input type="hidden" name="teacher_id" id="teacher_id" value="">
                            
                            <div class="col-md-6 form-group mt-4">
                                <center>
                                    <input type="submit" name="submit" value="Submit" class="btn btn-success">
                                </center>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if( isset($data['timetable_data']) )        
        <div class="card">
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="row">
                        <div class="col-md-12 form-group" id="printPage">
                            <div class="table-responsive">
                                   {!! App\Helpers\get_school_details() !!}
                                 <center><h6><b>Teacher Name : {{$data['teacher_data'][$data['teacher_id']]}}</b></h6></center>  
                            </div>
                        
  				<div class="col-md-12 form-group" id="printPage">
                     <table id="example" class="table table-striped">
                             <thead>
                            <tr>
                            <th class="text-center" ><span class="label label-info">Days/Lectures</span></th>
                              @foreach($data['period_data'] as $key => $value)
                              <th class="text-center" ><span class="label label-info">{{$value['title']}}</span></th>
                              @endforeach
                            </tr>
                            </thead>

                            <tbody id="get_data">
                            {{--   @if (!empty($data['timetable_data']) && count($data['timetable_data']) > 0) 
                                @foreach ($data['week_data'] as $wkey => $wval)
                                    <tr>
                                        <td style='display: table-cell;'><span class='label label-warning'>{{ $wkey }}</span></td>
                                        @foreach ($data['period_data'] as $pkey => $pval) 
                                            @php
                                                $value = null;
                                                $colspan = 1;
                                            @endphp
                                            @if (isset($data['timetable_data'][$wval][$pval['id']]['SUBJECT'])) 
                                                @foreach ($data['timetable_data'][$wval][$pval['id']]['SUBJECT'] as $k => $v) 
                                                    @php
                                    $currentBatch = $data['timetable_data'][$wval][$pval['id']]['BATCH'][$k] ?? '-';
                                    $currentSubject = data['timetable_data'][$wval][$pval['id']]['SUBJECT'][$k];
                                    $currentStd = $data['timetable_data'][$wval][$pval['id']]['STANDARD'][$k];
                                    $currentType = $data['timetable_data'][$wval][$pval['id']]['TYPE'][$k];
                                    $currentRoom = $data['timetable_data'][$wval][$pval['id']]['ROOM'][$k];
                                    
                                    $value .= $currentSubject .'<br>'. $currentBatch .'<br>'. $currentStd . '<br>' . $currentType . '<br>' . $currentRoom . '<br>';
                                                    @endphp
                                                @endforeach
                                                @if ($colspan > 1)
                                                    <td align='center' style='font-size:10px;color: black;' colspan="{{ $colspan }}">
                                                        {!! $value !!}
                                                    </td>
                                                @else
                                                    <td align='center' style='font-size:10px;color: black;'>{!! $value !!}</td>
                                                @endif
                                            @else 
                                                <td colspan="1">
                                                    <font color='red' style='font-size:10px;'>--No Period--</font>
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                                @else
                                <tr><td align='center' style='text-align: center;'>No Records Found!</td></tr>
                                @endif --}}
                   @if (!empty($data['timetable_data']) && count($data['timetable_data']) > 0)
    @foreach ($data['week_data'] as $wkey => $wval)
        <tr>
            <td style='display: table-cell;'><span class='label label-warning'>{{ $wkey }}</span></td>
            @php
                $prevSubject = null;
                $prevStd = null;
            @endphp

            @foreach ($data['period_data'] as $pkey => $pval)
                @php
                    $value = null;
                    $colspan = 1;
                @endphp

                @if (isset($data['timetable_data'][$wval][$pval['id']]['SUBJECT']))
                    @foreach ($data['timetable_data'][$wval][$pval['id']]['SUBJECT'] as $k => $v)
                        @php
                            $currentBatch = $data['timetable_data'][$wval][$pval['id']]['BATCH'][$k] ?? '-';
                            $currentSubject = $data['timetable_data'][$wval][$pval['id']]['SUBJECT'][$k];
                            $currentStd = $data['timetable_data'][$wval][$pval['id']]['STANDARD'][$k];
                            $currentType = $data['timetable_data'][$wval][$pval['id']]['TYPE'][$k];
                            $currentRoom = $data['timetable_data'][$wval][$pval['id']]['ROOM'][$k];

                            // Check if the current values match the previous values
                            if ($currentSubject == $prevSubject && $currentStd == $prevStd) {
                                $colspan++;
                            } else {
                                $colspan = 1; // Reset colspan if the values are different
                            }

                            $prevSubject = $currentSubject;
                            $prevStd = $currentStd;

                            $value .= $currentSubject . '<br>' . $currentBatch . '<br>' . $currentStd . '<br>' . $currentType . '<br>' . $currentRoom . '<br>';
                        @endphp
                    @endforeach

                    @if ($colspan > 1)
                        <td align='center' style='font-size:10px;color: black;' colspan="{{ $colspan }}">
                            {!! $value !!}
                        </td>
                    @else
                        <td align='center' style='font-size:10px;color: black;'>{!! $value !!}</td>
                    @endif
                @else
                    <td colspan="1">
                        <font color='red' style='font-size:10px;'>--No Period--</font>
                    </td>
                @endif

            @endforeach
        </tr>
    @endforeach
@else
    <tr>
        <td align='center' style='text-align: center;'>No Records Found!</td>
    </tr>
@endif

                     </tbody>
                    </table>
                </div>
                </div>
                    </div>
                </div>

                    <div class="col-md-12 form-group">
                        <center>
                            <button class="btn btn-success" onclick="PrintDiv('printPage');">Print</button>
                        </center>
                    </div>
                @endif

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


<!-- Your HTML content below -->
<script>
    // Function to copy emp_id to teacher_id when form is submitted
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            const empId = document.querySelector('select[name="emp_id"]').value;
            document.getElementById('teacher_id').value = empId;
        });
    });

    function removeTD(tdId){
        console.log(tdId);
    }

    function PrintDiv(divName) {
        var divToPrint = document.getElementById(divName);
        var popupWin = window.open('', '_blank', 'width=300,height=300');
        popupWin.document.open();
        popupWin.document.write('<html>');
        popupWin.document.write('<body onload="window.print()">' + divToPrint.innerHTML + '</html>');
        popupWin.document.close();
    }
</script>
@include('includes.footer')