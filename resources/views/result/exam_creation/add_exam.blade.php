@include('../includes.headcss')
@include('../includes.header')
@include('../includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Create Exam</h4>
            </div>            
        </div>      
        <div class="card">
            @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>
            </div>
            @endif
            <div class="col-lg-12 col-sm-12 col-xs-12">
                <form action="{{ route('exam_creation.store') }}" enctype="multipart/form-data" method="post">
                    {{ method_field("POST") }}
                    {{csrf_field()}}
                    <div class="row">
                        <!-- Term dropdown -->
                        {{ App\Helpers\TermDD() }}

                        <input type="hidden" name="medium" value="Institute"> 

                        <!-- Grade/Std/Division dropdown -->
                        {{ App\Helpers\SearchChain('4','single','grade,std') }} 

                        <div class="col-md-4 form-group">
                            <label for="subject">Select Subject:</label>
                            <select name="subject" id="subject" class="form-control" required>
                                <option value="">Select</option>
                            </select>
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="exam">Select Exam:</label>
                            <select name="exam" id="exam" class="form-control" required>
                                <option value="">Select</option>
                            </select>
                        </div>

                        <div class="col-md-12 form-group">
                            <div class="table-responsive">
                                <table id="examTable" class="table table-striped table-bordered order-list">
                                    <thead>
                                        <tr>
                                            <th>Select CO</th>
                                            <th>Name</th>
                                            <th>Marks</th>
                                            <th>Sort Order</th>
                                            <th class="text-left">Exam Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="co_id[]" class="form-control co-dropdown">
                                                    <option value="">Select</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="title[]" class="form-control" />
                                            </td>
                                            <td>
                                                <input type="text" name="points[]" class="form-control" />
                                            </td>
                                            <td>
                                                <input type="text" name="sort_order[]" class="form-control" />
                                            </td>
                                            <td>
                                                <input type="text" name="exam_date[]" class="form-control mydatepicker" autocomplete="off" />
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-success btn-sm addRow">+</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <input type="hidden" name="con_point" value="0">
                        <input type="hidden" name="app_disp_status" value="Y">
                        <input type="hidden" name="marks_type[]" value="MARKS">
                        <input type="hidden" name="report_card_status[]" value="Y">

                        <div class="col-md-12 form-group">
                            <center>
                                <input type="submit" name="submit" value="Save" class="btn btn-success">
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
    const requiredFields = ["#grade", "#standard", "#division", "#subject", "#term", "#exam"];
    requiredFields.forEach(field => $(field).prop('required', true));

    $('#term').change(function () {
        ["#standard", "#division", "#subject", "#exam"].forEach(field => {
            $(field).val("").empty().append('<option value="">Select</option>');
        });
    });

    $('#grade').change(function () {
        ["#subject", "#exam"].forEach(field => {
            $(field).empty().append('<option value="">Select</option>');
        });
    });

    $('#standard').change(function () {
        $("#exam").empty();
        var standardID = $("#standard").val();
        if (standardID) {
            $.ajax({
                type: "GET",
                url: "/api/get-subject-list?standard_id=" + standardID,
                success: function (res) {
                    if (res) {
                        $("#subject").empty().append('<option value="">Select</option>');
                        $.each(res, function (key, value) {
                            $("#subject").append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                }
            });
        }
    });

    $('#standard, #term').on('change', function () {
        var standardID = $("#standard").val();
        var termID = $("#term").val();
        if (standardID && termID) {
            $.ajax({
                type: "GET",
                url: "/api/get-exam-master-list?standard_id=" + standardID + "&term_id=" + termID,
                success: function (res) {
                    if (res) {
                        $("#exam").empty().append('<option value="">Select</option>');
                        $.each(res, function (key, value) {
                            $("#exam").append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                }
            });
        }
    });

    $('#subject').on('change', function () {
        var grade = $("#grade").val();
        var standard = $("#standard").val();
        var subject = $("#subject").val();

        if (grade && standard && subject) {
            $.ajax({
                type: "GET",
                url: "/getCOData?grade_id=" + grade + "&standard_id=" + standard + "&subject_id=" + subject,
                success: function (res) {
                    if (res) {
                        $(".co-dropdown").each(function(){
                            $(this).empty().append('<option value="">Select</option>');
                            $.each(res, function (key, value) {
                                $(this).append('<option value="' + value.id + '">' + value.short_code + '</option>');
                            }.bind(this));
                        });
                    }
                }
            });
        }
    });

    // Add/remove row functionality
    $(document).on("click", ".addRow", function () {
        var row = $(this).closest("tr").clone();
        row.find("input").val("");
        row.find("select").val("");
        row.find(".addRow")
            .removeClass("btn-success addRow")
            .addClass("btn-danger removeRow")
            .text("-");
        $("#examTable tbody").append(row);
    });

    $(document).on("click", ".removeRow", function () {
        $(this).closest("tr").remove();
    });
</script>

@include('includes.footer')
