@include('../includes.headcss')
@include('../includes.header')
@include('../includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">       
            <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">WRT Progress Report</h4> 
                </div>                
            </div>
            <div class="card">
                @if(!empty($data['message']))
                <div class="alert alert-{{ $data['class'] }} alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $data['message'] }}</strong>
                </div>
                @endif
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <form action="{{ route('WRT_progress_report.show_result') }}" enctype="multipart/form-data" method="post">
                        {{ method_field("POST") }}
                        {{csrf_field()}}
                        
                        <div class="row">
                            {{ App\Helpers\SearchChain('4','single','grade,std,div') }}                                                    

                            <div class="col-md-4 form-group mr-0 ml-0">
                                <label>From Date</label>
                                <input type="text" id="from_date" @if(isset($data['from_date'])) value="{{$data['from_date']}}"  @endif name="from_date" class="form-control mydatepicker" required="required" autocomplete="off">
                            </div>

                            <div class="col-md-4 form-group mr-0 ml-0">
                                <label>To Date</label>
                                <input type="text" id="to_date" @if(isset($data['to_date'])) value="{{$data['to_date']}}"  @endif name="to_date" class="form-control mydatepicker" required="required" autocomplete="off">
                            </div>

                            <div class="col-md-4 form-group mr-0 ml-0">
                                <label>Exam Type</label>                            
                                <select name="exam_type" id="exam_type" class="form-control">
                                    <option value="">Select</option>

                                </select>
                            </div>

                            <div class="col-md-12 form-group">
                                <center>
                                    <input type="submit" name="submit" value="Search" class="btn btn-success" >
                                </center>
                            </div>

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
    $("#grade").prop('required', true);
    $("#standard").prop('required', true);
    $("#division").prop('required', true);
    $("#co_scholastic_parent").prop('required', true);
    $("#term").prop('required', true);
    $("#co_scholastic").prop('required', true);
    $('#term').change(function () {
        $("#grade").val("");
        $("#standard").empty();
        $("#standard").append('<option value="">Select</option>');
        $("#division").empty();
        $("#division").append('<option value="">Select</option>');
        $("#co_scholastic_parent").empty();
        $("#co_scholastic_parent").append('<option value="">Select</option>');
        $("#co_scholastic").empty();
        $("#co_scholastic").append('<option value="">Select</option>');
    });
    $('#grade').change(function () {
        $("#co_scholastic_parent").empty();
        $("#co_scholastic_parent").append('<option value="">Select</option>');
        $("#co_scholastic").empty();
        $("#co_scholastic").append('<option value="">Select</option>');
    });
    $('#standard').change(function () {
        $("#co_scholastic_parent").empty();
        $("#co_scholastic_parent").append('<option value="">Select</option>');
        $("#co_scholastic").empty();
        $("#co_scholastic").append('<option value="">Select</option>');
        
        var std_id = $("#standard").val();
        var termID ={{session()->get('term_id')}};

        if (std_id && termID) {
            $.ajax({
                type: "GET",
                url: "/api/get-exam-master-list?standard_id=" + std_id +
                        "&term_id=" + termID,
                success: function (res) {
                    if (res) {
                        $("#exam_type").empty();
                        $("#exam_type").append('<option value="">Select</option>');
                        $.each(res, function (key, value) {
                            $("#exam_type").append('<option value="' + key + '">' + value + '</option>');
                        });

                    } else {
                        $("#exam_type").empty();
                    }
                }
            });
        } else {
            $("#exam_type").empty();
            $("#exam_type").append('<option value="">Select</option>');
            if (termID == "") {
                alert("Please Select Term.");
            }
        }
    });
    $('#division').on('change', function () {
        $("#co_scholastic").empty();
        $("#co_scholastic").append('<option value="">Select</option>');
        var standardID = $("#standard").val();
        if (standardID) {
            $.ajax({
                type: "GET",
                url: "/api/get-co-scholastic-parent-list?standard_id=" + standardID,
                success: function (res) {
                    if (res) {
                        $("#co_scholastic_parent").empty();
                        $("#co_scholastic_parent").append('<option value="">Select</option>');
                        $.each(res, function (key, value) {
                            $("#co_scholastic_parent").append('<option value="' + key + '">' + value + '</option>');
                        });

                    } else {
                        $("#co_scholastic_parent").empty();
                    }
                }
            });
        } else {
            $("#co_scholastic_parent").empty();
        }

    });
    $('#co_scholastic_parent').on('change', function () {
        var standardID = $("#standard").val();
        var co_scholastic_parentID = $("#co_scholastic_parent").val();
        var termID = $("#term").val();

        if (standardID && co_scholastic_parentID && termID) {
            $.ajax({
                type: "GET",
                url: "/api/get-co-scholastic-list?standard_id=" + standardID +
                        "&co_scholastic_parent_id=" + co_scholastic_parentID + "&term_id=" + termID,
                success: function (res) {
                    if (res) {
                        $("#co_scholastic").empty();
                        $("#co_scholastic").append('<option value="">Select</option>');
                        $.each(res, function (key, value) {
                            $("#co_scholastic").append('<option value="' + key + '">' + value + '</option>');
                        });

                    } else {
                        $("#co_scholastic").empty();
                    }
                }
            });
        } else {
            $("#co_scholastic").empty();
            $("#co_scholastic").append('<option value="">Select</option>');
            if (termID == "") {
                alert("Please Select Term.");
            }
        }

    });
</script>
@include('includes.footer')
