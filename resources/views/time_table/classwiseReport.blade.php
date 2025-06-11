@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<style>
    .title {
        font-weight: 200;
    }
</style>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">View Classwise Timetable</h4>
            </div>
        </div>

   @php
            $grade_id = $standard_id = $division_id = $enrollment_no = $receipt_no = $from_date = $to_date = '';

            if(isset($data['grade_id'])){
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
        @endphp

        <div class="card">
            @if ($message = Session::get('data'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message['message'] }}</strong>
            </div>
            @endif
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <form action="{{ route('classwise_timetable.create') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row ">
                            <div class="col-md-12 form-group">
                                <div class="row ">
									{{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}
                                </div>
                            </div>
                            <div class="col-md-12 form-group">
                                <center>
                                    <input type="submit" name="submit" value="Submit" class="btn btn-success">
                                </center>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if( isset($data['old_timetable_data']) )

        <div class="card">
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="row">
                        {!! App\Helpers\get_school_details("$grade_id","$standard_id","$division_id") !!}

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

                                    <tbody>
                                   @php $j = 0; @endphp
                                    @if(isset($data['week_data']))
                                        @foreach($data['week_data'] as $fullday => $wval)
                                            <tr>
                                                <td style='display: table-cell;'><span class='label label-warning'>{{ $fullday }}</span></td>
                                                @foreach($data['period_data'] as $key => $pval)
                                                    
                                                    <td class="text-center" style="font-size:10px;color: black;" colspan="{{ $j }}">
                                                       @if (isset($data['old_timetable_data'][$wval][$pval['id']]['SUBJECT']) && count($data['old_timetable_data'][$wval][$pval['id']]['SUBJECT']) >0) 

                                                       @php
                                                        $periodData =$data['old_timetable_data'][$wval][$pval['id']]; 
                                                        $subjectCount = count($data['old_timetable_data'][$wval][$pval['id']]['SUBJECT']);
                                                        @endphp
                                                            @foreach ($periodData['SUBJECT'] as $k => $subject_name)
                                                                {{ $subject_name . (isset($periodData['BATCH'][$k]) ? " / " . $periodData['BATCH'][$k] : '') }}
                                                                @if (isset($periodData['TEACHER'][$k]))<br>{{ $periodData['TEACHER'][$k] }}@endif
                                                                <br>{{ $periodData['TYPE'][$k] }}
                                                                @if ($k != ($subjectCount - 1))<br><hr>@endif
                                                            @endforeach
                                                        @else
                                                            <font color='red' style='font-size:10px;'>--No Period--</font>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endif

                                    </tbody>
                            </table>
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
<script>
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
