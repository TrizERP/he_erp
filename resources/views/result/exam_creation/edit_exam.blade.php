@extends('layout')
@section('container')

<div id="page-wrapper">
    <div class="container-fluid">        
            <div class="card">
                @if ($message = Session::get('success'))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $message }}</strong>
                </div>
                @endif
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <form action="{{ route('exam_creation.update', $data['id']) }}" enctype="multipart/form-data" method="post">
                        {{ method_field("PUT") }}
                        {{csrf_field()}}
                        <div class="row">
                            <!-- Below function will get term name and id from helper.php  -->
                            {{ App\Helpers\TermDD($data['term_id'],'3') }}
                            <!-- Below function will get grade,standard,division name and id from helper.php  -->
                            <div class="col-md-9">
                                {{ App\Helpers\SearchChainSubject('4','single','grade,std,sub',$data['grade'],$data['standard_id'],$data['subject_id']) }}
                            </div>
                            <input type="hidden" value="{{$data['medium']}}" name="medium">

                            <div class="col-md-4 form-group">
                                <label>Exam Type : </label>
                                <select name="exam_id" class="form-control" id="exam">
                                    <option value="">Select</option>
                                    @foreach ($data['exams'] as $key => $value)
                                    <option value="{{ $key }}"
                                            @if ($data['exam_id'] == $key)
                                            selected="selected"
                                            @endif
                                            >{{ $value }}</option>
                                    @endforeach

                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Select CO : </label>
                                <select name="co_id" class="form-control" id="co_id">
                                    <option value="">Select</option>
                                  
                                </select>
                            </div>
                            <input type="hidden" value="{{$data['con_point']}}" name="con_point">
                            <input type="hidden" value="{{$data['app_disp_status']}}" name="app_disp_status">

                            <div class="col-md-3 form-group hide">
                                <label for="report_card_status">Report Card Status</label>
                                <select name="report_card_status" id="report_card_status" class="form-control">
                                    @foreach($data['report_card_status_arr'] as $key=>$value)
                                    <option value="{{$key}}" @if(isset($data['report_card_status']) && $data['report_card_status']==$key) Selected @endif>{{$value}}</option>
                                    @endforeach
                                </select>
                            </div>
                                <div class="col-md-12 form-group mt-2">
                                <table id="myTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Marks</th>
                                        <th>Sort Order</th>
                                        <th>Exam Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" name="title" value="{{ $data['title'] }}" class="form-control" />
                                        </td>
                                        <td>
                                            <input type="text" name="points" value="{{ $data['points'] }}" class="form-control" />
                                        </td>
                                        
                                        {{-- <input type="hidden" value="{{$data['report_card_status']}}" name="report_card_status"> --}}
                                        <input type="hidden" value="{{$data['marks_type']}}" name="marks_type">
                                        
                                        <td>
                                            <input type="text" name="sort_order" value="{{ $data['sort_order'] }}" class="form-control" />
                                        </td>
                                        <td>
                                            <input type="text" name="exam_date" value="{{ $data['exam_date'] }}" class="form-control mydatepicker" autocomplete="off" />
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                </tr>
                                <tr>
                                </tr>
                            </tfoot>
                        </table>
                        </div>

                        <div class="col-md-12 form-group">
                            <center>
                                <input type="submit" name="submit" value="Save" class="btn btn-success" >
                            </center>
                        </div>

                        </form>
                        </div>
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


@include('includes.footerJs')
<script>
const requiredFields = ["#grade", "#standardS", "#division", "#subject", "#term", "#exam"];
    requiredFields.forEach(field => $(field).prop('required', true));

    $('#term').change(function () {
        ["#standardS","#subject", "#exam", "#co_id"].forEach(field => {
            $(field).val("").empty().append('<option value="">Select</option>');
        });
        $('#gradeS').val('');
    });

    $('#gradeS').change(function () {
        ["#subject", "#exam", "#co_id"].forEach(field => {
            $(field).empty().append('<option value="">Select</option>');
        });
    });
    $('#standardS').change(function () {
        $("#exam").empty();
        $('#co_id').empty();
        $("#exam").append('<option value="">Select</option>');
        var standardID = $("#standard").val();
        if (standardID) {
            $.ajax({
                type: "GET",
                url: "/api/get-subject-list?standard_id=" + standardID,
                success: function (res) {
                    if (res) {
                        $("#subject").empty();
                        $("#subject").append('<option value="">Select</option>');
                        $.each(res, function (key, value) {
                            $("#subject").append('<option value="' + key + '">' + value + '</option>');
                        });

                    } else {
                        $("#subject").empty();
                    }
                }
            });
        } else {
            $("#subject").empty();
        }

    });
    $('#standardS').on('change', function () {
        var standardID = $("#standardS").val();
        var termID = $("#term").val();

        if (standardID && termID) {
            $.ajax({
                type: "GET",
                url: "/api/get-exam-master-list?standard_id=" + standardID +
                        "&term_id=" + termID,
                success: function (res) {
                    if (res) {
                        $("#exam").empty();
                        $("#exam").append('<option value="">Select</option>');
                        $.each(res, function (key, value) {
                            $("#exam").append('<option value="' + key + '">' + value + '</option>');
                        });

                    } else {
                        $("#exam").empty();
                    }
                }
            });
        } else {
            $("#exam").empty();
            $("#exam").append('<option value="">Select</option>');
            if (termID == "") {
                alert("Please Select Term.");
            }
        }

    });

    $('#subject').on('change',function(){
        $('#co_id').empty();
        var grade = $("#gradeS").val();
        var standard = $("#standardS").val();
        var subject = $(this).val();

        getCO(grade,standard,subject)
    })

    $(document).ready(function () {
        var grade = $("#gradeS").val();
        var standard = $("#standardS").val();
        var subject = $("#subject").val();
        var co_id = "{{ $data['co_id'] }}";
        // console.log(grade +'-'+ standard +'-'+ subject +'-'+ co_id);
        if (grade && standard && subject && co_id) {
            getCO(grade,standard,subject,co_id)
        }
    });

    function getCO(grade,standard,subject,salVal='') {
        if (grade && standard && subject) {
            $.ajax({
                type: "GET",
                url: "/getCOData?grade_id="+grade+"&standard_id=" + standard +"&subject_id=" + subject,
                success: function (res) {
                    console.log(res);
                    if (res) {
                        $("#co_id").empty();
                        $("#co_id").append('<option value="">Select</option>');
                        $.each(res, function (key, value) {
                            var selected = (salVal == value.id) ? 'selected' : '';
                            $("#co_id").append('<option value="' + value.id + '" '+selected+'>' + value.title + '</option>');
                        });

                    } else {
                        $("#co_id").empty();
                    }
                }
            });
        } else {
            $("#co_id").empty();
            $("#co_id").append('<option value="">Select</option>');
        }
    }
</script>

@include('includes.footer')
@endsection