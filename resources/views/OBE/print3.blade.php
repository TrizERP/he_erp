@include('includes.headcss')
<style>
    body{
        margin:0;
        padding:0;
        background: #fff;
    }
    .schoolData{
        padding-top:40px;
    }
    .courseData{
        padding-top:25px;
    }
    .table-course, .table-levels{
        border : 2px solid #b5adad;
    }

    .table-course th,.table-course td{
        width:50%  !important;
    }
    .table-course th,.table-course td,
    .table-levels th,.table-levels td
    {
        font-weight: bold  !important;
        border : 2px solid #b5adad !important;
    }
    .studentTableDiv{
        width: 100%;
        padding:20px;
    }
</style>
    <div class="container">
        <div class="schoolData ">
            <center>
                <h1>{{session()->get('school_name')}}</h1>
                <h2>{{$data['addCourseData']->semester_name}}</h2>
            </center>
        </div>
        <div class="courseData">
            <center>
                <h3><b>Mid, Remid and Internal Marksheet</b></h3>
                <table class="table table-bordered table-course">
                    @php
                        $fields = [
                            'Academic Year' => $data['addCourseData']->academic_year,
                            'Course' => $data['addCourseData']->course_name,
                            'GTU Course Code' => $data['addCourseData']->course_code,
                            'Course Code as per NBA file' => $data['addCourseData']->course_code_nba,
                            'Course Co-ordinator' => $data['addCourseData']->course_coordinator,
                            'Other Course Teacher' => $data['addCourseData']->subject_teachers,
                        ];
                    @endphp
                    @foreach ($fields as $label => $value)
                        <tr>
                            <th class="text-right">{{ $label }}:</th>
                            <td>{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
            </center>
        </div>
    </div>
    @php 
    $coData = isset($data['coData']->all_co_id) ? explode('||',$data['coData']->all_co_id) : [];
    $levels = [3,2,1,0];
    $j=1;
    @endphp
    <div class="studentTableDiv">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th rowspan="3" style="align-content: space-evenly;">Sr No.</th>
                <th rowspan="3" style="align-content: space-evenly;">Enrollment No.</th>
                <th rowspan="3" style="align-content: space-evenly;">Name of the student</th>
                <th colspan="{{count($coData)+1}}" class="text-center">MSE</th>
                <th colspan="{{count($coData)+1}}" class="text-center">Re-MSE</th>
                <th colspan="{{count($coData)+1}}" class="text-center">Internal</th>
            </tr>
            <tr>
                @for($i=0;$i<3;$i++)
                    @foreach($coData as $k=>$v)
                    <th>CO{{$k+1}}</th>
                    @endforeach
                    <th class="text-left">Total</th>
                @endfor
            </tr>
            <tr>
                @for($i=0;$i<3;$i++)
                    @php $coTotal = 0; @endphp
                    @foreach($coData as $k=>$v)
                        @php 
                            $thVal = 5;
                            if($i==2 && in_array($k,[3,5])){
                                $thVal = 2;
                            }
                            else if($i==2){
                                $thVal = 4;
                            }
                            $coTotal +=$thVal;
                        @endphp
                        
                        <th>{{$thVal}}</th>
                    @endforeach
                    <th class="text-left">{{$coTotal}}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($data['studentMarks'] as $studentId => $value)
            <tr>
                <td>{{$j++}}</td>
                <td>{{$value['enrollment_no']}}</td>
                <td>{{$value['name']}}</td>
                @php 
                $mseTotal = $remseTotal = $internalTotal = 0;
                @endphp
                @foreach($coData as $k=>$v)
                    @php 
                     $midSemMarks = isset($value['Mid semester Exam'][$v]->obt_marks) ? $value['Mid semester Exam'][$v]->obt_marks : '';
                     $mseTotal += isset($value['Mid semester Exam'][$v]->obt_marks) ? $value['Mid semester Exam'][$v]->obt_marks : 0;
                    @endphp
                    <td>{{ $midSemMarks }}</td>
                @endforeach
                <td>{{$mseTotal}}</td>

                @foreach($coData as $k=>$v)
                    <td></td>
                @endforeach
                <td>{{$remseTotal}}</td>

                @foreach($coData as $k=>$v)
                    @php 
                     $inertnalMarks = isset($value['INTERNAL ASSESSMENT'][$v]->obt_marks) ? $value['INTERNAL ASSESSMENT'][$v]->obt_marks : '';
                     $internalTotal += isset($value['INTERNAL ASSESSMENT'][$v]->obt_marks) ? $value['INTERNAL ASSESSMENT'][$v]->obt_marks : 0;
                    @endphp
                    <td>{{ $inertnalMarks }}</td>
                @endforeach
                <td>{{$internalTotal}}</td>
            </tr>
            @endforeach
        </tbody>
        </table>
    </div>

@include('includes.footer')
</body>
</html>