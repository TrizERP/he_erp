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
    .levelWiseCO,.attainment, .poPosDiv, .normalizedDiv{
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-evenly;
        margin-top: 30px;
        padding:20px;
    }
    .levelStudentWise,
    .levelPercentageWise,
    .coAttaiment,
    .averageCoAttaiment{
        width:50%;
        padding:0px 20px;
    }

    .signatureDiv{
        width:100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-evenly;
        padding-top:60px;
        padding-bottom:10px;
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
                <h3><b>COs, POs and PSOs attaintment</b></h3>
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

    <div class="levelWiseCO">
        @php 
        $coData = isset($data['coData']->all_co_id) ? explode('||',$data['coData']->all_co_id) : [];
        $levels = [3,2,1,0];
        $studentWiseCO = $percentagewiseCo = $processedStudents = [];

        foreach ($data['studentMarks'] as $studentId => $value) {
           foreach ($value as $titles => $examArr) {
            if(is_array($examArr)){
            foreach ($examArr as $key => $val) {
                foreach ($coData as $coKey => $co_id) {
                    $uniqueKey = $studentId . '-' . $co_id;
                    if (!isset($processedStudents[$uniqueKey])) {
                        if($co_id == $val->co_id){
                            $level = App\Helpers\getGrade($data['gradeScale'], $val->points, $val->obt_marks);
                           
                            if (!isset($studentWiseCO[($coKey) + 1][$level])) {
                                $studentWiseCO[($coKey) + 1][$level] = 0;
                            }
                            $studentWiseCO[($coKey) + 1][$level] += 1;
                            $processedStudents[$uniqueKey] = true;
                        }
                    }
                }
            }
            }
           }
        }
        // echo "<pre>";print_r($percentagewiseCo);exit;
        @endphp
        <div class="levelStudentWise">
            <h4 class="text-center">Level wise CO attainment  (Studentwise)</h4>
            <table class="table table-bordered table-levels" width="100%">
                <thead>
                    <tr>
                        <th>Levels</th> 
                        @foreach ($coData as $coOrder)
                            <th class="text-left">CO{{$coOrder}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($levels as $k=>$level)
                    <tr>
                        <td>{{$level}}</td>
                        @foreach ($coData as $coOrder=>$coId)
                        <td class="text-left">{{ isset($studentWiseCO[($coOrder+1)][$level]) ? $studentWiseCO[($coOrder+1)][$level] : 0}}</td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Total</td>
                        @foreach ($coData as $coOrder=>$coId)
                        <td class="text-left">{{ count($data['studentMarks'])}} </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="levelPercentageWise">
            <h4 class="text-center">Level wise CO attainment (in Percentage)</h4>
            <table class="table table-bordered table-levels" width="100%">
                <thead>
                    <tr>
                        <th>Levels</th>
                        @foreach ($coData as $coOrder)
                            <th class="text-left">CO{{$coOrder}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($levels as $k=>$level)
                    <tr>
                        <td>{{$level}}</td>
                        @foreach ($coData as $coOrder=>$coId)
                        @php 
                        $percentage = (isset($studentWiseCO[($coOrder+1)][$level]) && count($data['studentMarks'])>0)
                            ? ($studentWiseCO[($coOrder+1)][$level] / count($data['studentMarks'])) * 100 
                            : 0;
                        @endphp
                        <td class="text-left">{{ number_format($percentage,2) }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>%</td>
                       
                        @foreach ($coData as $coOrder=>$coId)
                        <td class="text-left">100</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="attainment">
        <div class="coAttaiment">
            <h4 class="text-center">CO attainment (Average)</h4>
            <table class="table table-bordered table-levels" width="100%">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        @foreach ($coData as $coOrder)
                            <th class="text-left">CO{{$coOrder}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <td>{{$data['addCourseData']->course_code}}</td>
                    @foreach ($coData as $coOrder)
                        <td class="text-left">2.17</td>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="averageCoAttaiment">
            <h4>&nbsp;</h4>
            <table class="table table-bordered table-levels" width="100%">
                <thead>
                    <tr>
                        <th class="text-center">Average Course  CO attainment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">2.18</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="poPosDiv">
        <h4 class="text-center">PO and PSO attainment (Average)</h4>
        <table class="table table-bordered table-levels" width="100%">
            <thead>
                <tr>
                    <th>Course Code</th>
                    @foreach ($data['poData'] as $poKey=> $poVal)
                     <th class="text-left">{{$poVal->short_code}}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Obtained attainment value</td>
                    @foreach ($data['poData'] as $poKey=> $poVal)
                        @php
                        $avgObt = $avgMarks = $avgPoints = 0;
                        $jsonData = [];
                        foreach ($data['co_po_mapped'] as $copoKey=> $copoVal) {
                            $jsonData = isset($copoVal->po_json) ? json_decode($copoVal->po_json,true) : [];
                            $avgMarks += isset($jsonData[($poKey+1)]) ? $jsonData[($poKey+1)] : 0;
                            $avgPoints += isset($jsonData[($poKey+1)]) ? 1 : 0;
                        }
                        $avgObt = ($avgMarks > 0) ? (($avgPoints*3) / 6) : 0;
                        @endphp
                        <td class="text-left">{{number_format($avgObt,2)}}</td>
                    @endforeach
                </tr>
                <tr>
                    <td>Max. attainable value</td>
                    @foreach ($data['poData'] as $poKey=> $poVal)
                        @php
                        $avgObt = $avgMarks = $avgPoints = 0;
                        $jsonData = [];
                        foreach ($data['co_po_mapped'] as $copoKey=> $copoVal) {
                            $jsonData = isset($copoVal->po_json) ? json_decode($copoVal->po_json,true) : [];
                            $avgMarks += isset($jsonData[($poKey+1)]) ? $jsonData[($poKey+1)] : 0;
                            $avgPoints += isset($jsonData[($poKey+1)]) ? 1 : 0;
                        }
                        $avgObt = ($avgMarks > 0) ? ($avgMarks / $avgPoints) : 0;
                        @endphp
                        <td class="text-left">{{number_format($avgObt,2)}}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    <div class="normalizedDiv">
        <table class="table table-bordered table-levels" width="100%">
            <thead>
                <tr class="header-row">
                    <th colspan="12" class="text-center">Normalised value (out of 3) - for Overall program attainment</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>C719</td>
                    <td>2.18</td>
                    <td>2.18</td>
                    <td>2.18</td>
                    <td>2.17</td>
                    <td>2.18</td>
                    <td>2.18</td>
                    <td></td>
                    <td></td>
                    <td>2.17</td>
                    <td>2.18</td>
                    <td>2.18</td>
                </tr>
               
            </tbody>
        </table>
    </div>

    <div class="signatureDiv">
        <div class="signature1">
            <div class="spaceDiv"></div>
            <label for=""><h4>Course Co-ordinator</h4></label>
        </div>
        <div class="signature2">
            <div class="spaceDiv"></div>
            <label for=""><h4>Program Assessment Commiittee</h4></label>
        </div>
        <div class="signature3">
            <div class="spaceDiv"></div>
            <label for=""><h4>Head of the Department</h4></label>
        </div>
    </div>

@include('includes.footer')
</body>
</html>