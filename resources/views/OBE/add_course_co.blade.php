@extends('layout')
@section('container')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-3 mt-2 col-sm-4 col-xs-12">
                <h4 class="page-title">Add Course Field CO</h4> 
            </div>
        </div>        
        <div class="card">
            @if ($sessionData = Session::get('data'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $sessionData['message'] }}</strong>
            </div>
            @endif

            @php
            $inputArray = [
                        ['academic_year', 'Academic Year', 'text', 'e.g., 2023-2024', true],
                        ['programme', 'Programme', 'text', 'e.g., B.Tech, M.Tech', true],
                        ['semester', 'Semester', 'select', '', true],
                        ['course', 'Course (Subject)', 'select', 'e.g., Mathematics, Physics', false],
                        ['course_code', 'GTU Course Code', 'text', 'GTU Course Code', false],
                        ['course_code_nba', 'Course Code as per NBA File', 'text', 'Course Code as per NBA File', false],
                        ['course_coordinator', 'Course Co-Ordinator', 'text', 'Course Co-Ordinator', false],
                        ['subject_teachers', 'Subject Teachers', 'text', 'e.g., John Doe, Jane Smith', false],
                        ['subject_types', 'L-T-L', 'text', 'e.g., 3-1-0', false],
                        ['no_student', 'Total No of Students', 'number', 'e.g., 60', false]
                    ];
                $tableArray = ['SR. No.', 'ACADEMIC YEAR', 'PROGRAMME', 'SEMESTER', 'COURSE (SUBJECT)', 'GTU COURSE CODE', 'COURSE CODE AS PER NBA FILE', 'COURSE CO-ORDINATOR', 'SUBJECT TEACHERS', 'L-T-L', 'TOTAL NO. STUDENT', 'ACTION'];

            @endphp

            <form action="{{ route('add_course_co.store') }}" method="POST">
                @csrf
                <div class="row">
                    @foreach($inputArray as $field)
                    <div class="col-md-3 mt-2">
                        <label for="{{ $field[0] }}">{{ $field[1] }}</label>
                        @if($field[2] === 'select' && $field[0] === 'semester')
                        <select name="{{ $field[0] }}" id="{{ $field[0] }}" class="form-control" {{ $field[4] ? 'required' : '' }}>
                            <option value="">select {{ strtolower($field[1]) }}</option>
                            @foreach($data['semesterLists'] as $id => $semester)
                                <option value="{{ $id }}">{{ $semester }}</option>
                            @endforeach
                        </select>
                        @elseif($field[2]==='select')
                            <select name="{{ $field[0] }}" id="{{ $field[0] }}" class="form-control" {{ $field[4] ? 'required' : '' }}>
                           
                            </select>
                        @else
                        <input type="{{ $field[2] }}" class="form-control" id="{{ $field[0] }}" name="{{ $field[0] }}" placeholder="{{ $field[3] }}" {{ $field[4] ? 'required' : '' }}>
                        @endif
                    </div>
                    @endforeach
                    <div class="col-md-12 mt-2">
                        <center>
                            <input type="submit" class="btn btn-primary" name="submit" value="Submit">
                        </center>
                    </div>
                </div>
            </form>
        </div>
        
            @if(isset($data['addedData']) && count($data['addedData']) > 0)
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-bordered" id="example">
                        <thead>
                            <tr>
                                @foreach($tableArray as $header)
                                    <th class='text-left'>{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['addedData'] as $key => $value)
                            <tr>
                                @foreach([$key+1, $value['academic_year'], $value['programme'], $data['semesterLists'][$value['semester']] ?? $value['semester'], $value['course_name'], $value['course_code'], $value['course_code_nba'], $value['course_coordinator'],$value['subject_teachers'], $value['subject_types'], $value['no_student']] as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                                <td>
                                    <a data-toggle="modal" data-target="#exampleModal" id="openModel" class="btn btn-info btn-outline edit-btn" 
                                       data-id="{{ $value['id'] }}" 
                                       data-academic_year="{{ $value['academic_year'] }}" 
                                       data-programme="{{ $value['programme'] }}" 
                                       data-semester="{{ $value['semester'] }}" 
                                       data-course="{{ $value['course'] }}" 
                                       data-course_code="{{ $value['course_code'] }}" 
                                       data-course_code_nba="{{ $value['course_code_nba'] }}" 
                                       data-course_coordinator="{{ $value['course_coordinator'] }}" 
                                       data-subject_teachers="{{ $value['subject_teachers'] }}" 
                                       data-subject_types="{{ $value['subject_types'] }}" 
                                       data-no_student="{{ $value['no_student'] }}">
                                        <i class="ti-pencil-alt"></i>
                                    </a>
                                    <form class="d-inline" action="{{ route('add_course_co.destroy', $value['id'])}}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-info btn-outline-danger" onclick="return confirmDelete();"><i class="ti-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width:1000px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Course Field CO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="editForm">
                    @csrf
                    {{ method_field('PUT') }}
                    <div class="row">
                        @foreach($inputArray as $field)
                        <div class="col-md-3 mt-2">
                            <label for="{{ $field[0] }}">{{ $field[1] }}</label>
                            @if($field[2] === 'select' && $field[0]=="semester")
                            <select name="{{ $field[0] }}" id="edit_{{ $field[0] }}" class="form-control" {{ $field[4] ? 'required' : '' }}>
                                <option value="">select {{ strtolower($field[1]) }}</option>
                                @foreach($data['semesterLists'] as $id => $semester)
                                    <option value="{{ $id }}" {{ isset($data['editData']) && $data['editData']['semester'] == $id ? 'selected' : '' }}>{{ $semester }}</option>
                                @endforeach
                            </select>
                            @elseif($field[2]==='select' && $field[0]=="course")
                            <select name="{{ $field[0] }}" id="edit_{{ $field[0] }}" class="form-control" {{ $field[4] ? 'required' : '' }}>
                                
                            </select>
                            @else
                            <input type="{{ $field[2] }}" class="form-control" id="edit_{{ $field[0] }}" name="{{ $field[0] }}" placeholder="{{ $field[3] }}" value="{{ isset($data['editData']) ? $data['editData'][$field[0]] : '' }}" {{ $field[4] ? 'required' : '' }}>
                            @endif
                        </div>
                        @endforeach
                        <div class="col-md-12 mt-2">
                            <center>
                                <input type="submit" class="btn btn-primary" name="submit" value="Save Changes">
                            </center>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('includes.footerJs')
<script>
    $(document).ready(function() {
        $('#semester').on('change', function() {
            var std_id = $(this).val();
           getSubject('course',std_id)
        });
         // For the edit form
         $('#edit_semester').on('change', function() {
            var std_id = $(this).val();
            getSubject('edit_course', std_id);
        });
    });

    function getSubject(div,std_id,selVal=''){
            $("#"+div).empty();
            var path = "{{ route('ajax_StandardwiseSubject') }}";
            $('#'+div).find('option').remove().end().append('<option value="">Select Subject</option>').val('');
            $.ajax({
                url: path,
                data:'std_id='+std_id, 
                success: function(result){
                    for(var i=0;i < result.length;i++){
                        var selected = '';
                        if(selVal!=='' && selVal===result[i]['subject_id']){
                            selected = 'selected';
                        }
                        $("#"+div).append($("<option "+selected+"></option>").val(result[i]['subject_id']).html(result[i]['display_name']));
                    }
                }
            });
    }
    </script>
<script>
    $(document).on('click', '#openModel', function () {
        const data = $(this).data();
        $('#editForm').attr('action', `{{ route('add_course_co.update', '') }}/${data.id}`);
        Object.keys(data).forEach(key => {
            if (key !== 'id') {
                $(`#edit_${key}`).val(data[key]);
                if(key=="course"){
                    getSubject('edit_course', data.semester,data.course);
                }
            }
        });
    });

    $(document).ready(function () {
        $('#example').DataTable({
            select: true,
            lengthMenu: [[100, 500, 1000, -1], ['100', '500', '1000', 'Show All']],
            dom: 'Bfrtip',
            buttons: ['pdfHtml5', 'csv', 'excel', 'print', 'pageLength']
        });
    });
</script>
@include('includes.footer')
@endsection
