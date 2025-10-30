@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
                <h4 class="page-title">Subjectwise Detailed Semester Report</h4>
            </div>
        </div>

        @php
            $grade_id = $standard_id = $division_id = '';
            if (isset($data['grade_id'])) {
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            $getInstitutes = session()->get('getInstitutes');
        @endphp

        <div class="card">
            @if ($sessionData = Session::get('data'))
                @if ($sessionData['status_code'] == 1)
                    <div class="alert alert-success alert-block">
                @else
                    <div class="alert alert-danger alert-block">
                @endif
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $sessionData['message'] }}</strong>
                </div>
            @endif

            <form action="{{ route('show_monthwise_student_attendance_report') }}" enctype="multipart/form-data" method="post">
                @csrf
                <div class="row">
                    {{ App\Helpers\SearchChain('3', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}

                    <div class="col-md-3 form-group">
                        <label for="">Type</label>
                        <select name="lecture_type" id="lecture_type" class="form-control" required>
                            <option value="">-Select Type-</option>
                            @foreach ($data['types'] as $k => $value)
                                <option value="{{ $value }}" @if(isset($data['lecture_type']) && $data['lecture_type']==$value) selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 form-group">
                        <label for="">Subject</label>
                        <select name="subject" id="subject" class="form-control" required>
                            <option value="">-Select Subject-</option>
                        </select>
                    </div>

                    @if (isset($data['batch']) && !empty($data['batchs']))
                        <div class="col-md-3 form-group" id="batch_div">
                            <label>-Select Batch-</label>
                            <select name="batch_sel" class="form-control" id="batch_sel" required="">
                                @foreach ($data['batchs'] as $batch)
                                    <option value="{{ $batch->id }}" @if ($data['batch_id'] == $batch->id) selected @endif>
                                        {{ $batch->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-12 form-group">
                        <center>
                            <input type="submit" name="submit" value="Search" class="btn btn-success">
                        </center>
                    </div>
                </div>
            </form>
        </div>

        @if (isset($data['student_data']))
            @php
                $j = 1;
                if (isset($data['student_data'])) {
                    $student_data = $data['student_data'];
                }
            @endphp

            <div class="card">
                {{-- School header/address --}}
                @php
                    echo App\Helpers\get_school_details($grade_id, $standard_id, $division_id);

                     $getInstitutes = session()->get('getInstitutes');
                     $academicYears = session()->get('academicYears');
                     $syear = session()->get('syear');


            $nextYear = $syear + 1;

                @endphp

                {{-- ✅ Academic Year Label (same font as address) --}}
           
                <center>
                    <span style="font-size: 15px; font-weight: 600; font-family: Arial, Helvetica, sans-serif !important; display:block; margin-top: 15px; margin-bottom: 5px;">
                        Academic Year :{{ $syear }} - {{ $nextYear }}
                    </span>
                </center>
               

                {{-- Report Title --}}
                <h1 style="text-align:center; font-size:20px; margin-top:5px; font-family:inherit; color:black;">
                    Subjectwise Detailed Semester Report
                </h1>

                <div class="table-responsive">
                    <table id="example" class="table display" style="border:none !important">
                        <thead>
                            <tr id="heads">
                                <th>{{ App\Helpers\get_string('grno', 'request') }}</th>
                                <th>{{ App\Helpers\get_string('studentname', 'request') }}</th>
                                @foreach($data['dateArr'] as $key => $date)
                                    <th>{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
                                @endforeach
                                <th>Total</th>
                                <th>Present</th>
                                <th>Absent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student_data as $stu)
                                @php
                                    $total = $present = $absent = 0;
                                @endphp
                                <tr>
                                    <td>{{ $stu->enrollment_no }}</td>
                                    <td>{{ $stu->student_name }}</td>
                                    @foreach($data['dateArr'] as $key => $date)
                                        @php
                                            $code = $data['studentArr'][$stu->student_id][$key] ?? '-';
                                            if($code != '-') $total++;
                                            if($code == 'P') $present++;
                                            if($code == 'A') $absent++;
                                        @endphp
                                        <td>{!! $code == 'A' ? '<b>A</b>' : $code !!}</td>
                                    @endforeach
                                    <td>{{ $total }}</td>
                                    <td>{{ $present }}</td>
                                    <td>{{ $absent }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Signature Section --}}
                    <div style="margin-top:60px; padding:20px 0; width:100%; color:black">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%;">
                            <div style="text-align:left; width:33%;">
                                <div style="border-top:1px solid #000; padding-top:5px; display:inline-block;">
                                    Sign of Class Coordinator
                                </div>
                            </div>
                            <div style="text-align:center; width:33%;">
                                <div style="border-top:1px solid #000; padding-top:5px; display:inline-block;">
                                    Sign of HOD
                                </div>
                            </div>
                            <div style="text-align:right; width:33%;">
                                <div style="border-top:1px solid #000; padding-top:5px; display:inline-block;">
                                    Sign of Principal
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@include('includes.footerJs')
@include('includes.footer')
