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
    $j=1;
    @endphp

    <div class="studentTableDiv">
        <table class="table table-bordered studentTable">
            <thead>
                <tr>
                    <th rowspan="2" style="align-content: space-evenly;text-align:center">Sr No.</th>
                    <th rowspan="2" style="align-content: space-evenly;text-align:center">Enrollment No</th>
                    <th rowspan="2" style="align-content: space-evenly;text-align:center">Name</th>
                    <th style="align-content: space-evenly;text-align:center">Mid</th>
                    <th style="align-content: space-evenly;text-align:center">Remid</th>
                    <th class="text-left">Internal</th>
                </tr>
                <tr>
                    <th style="align-content: space-evenly;text-align:center">30</th>
                    <th style="align-content: space-evenly;text-align:center">30</th>
                    <th style="align-content: space-evenly;text-align:center" class="text-left">20</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['studentMarks'] as $studentId => $value)
                <tr>
                    <td>{{$j++}}</td>
                    <td>{{$value['enrollment_no']}}</td>
                    <td>{{$value['name']}}</td>
                    @php 
                        $mid = $remid = $internal = 0;
                    @endphp
                    @foreach($coData as $k => $v)
                        @php
                            $mid += isset($value['Mid semester Exam'][$v]->obt_marks) ? $value['Mid semester Exam'][$v]->obt_marks : 0;
                            $internal += isset($value['INTERNAL ASSESSMENT'][$v]->obt_marks) ? $value['INTERNAL ASSESSMENT'][$v]->obt_marks : 0;
                        @endphp
                    @endforeach
                    <td>{{$mid}}</td>
                    <td></td>
                    <td>{{$internal}}</td>
                <tr> 
                @endforeach
            </tbody>
        </table>
    </div>
    
@include('includes.footer')
</body>
</html>