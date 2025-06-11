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
                        <!-- Below function will get term name and id from helper.php  -->                            
                        {{ App\Helpers\TermDD() }}

                            <input type="hidden" name="medium" value="CBSE" > 

                            <!-- Below function will get grade,standard,division name and id from helper.php  -->                            
                            {{ App\Helpers\SearchChain('4','single','grade,std') }} 
                            
                            <div class="col-md-4 form-group">
                                <label for="title" >Select Subject:</label>
                                <select name="subject" id="subject" class="form-control">
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label for="title">Select Exam:</label>
                                <select name="exam" id="exam" class="form-control">
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label for="co_id">Select CO:</label>
                                <select name="co_id" id="co_id" class="form-control">
                                    <option value="">Select</option>
                                </select>
                            </div>

                                <input type="hidden" name="con_point" class="form-control" value="0">
                         
                                <input type="hidden" name="app_disp_status" value="Y" > 

                            <div class="col-md-12 form-group">
                            <div class="table-responsive">
                                <table id="myTable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Marks</th>
                                            <th>Sort Order</th>
                                            <th class="text-left">Exam Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="text" name="title" class="form-control" />
                                            </td>
                                            <td>
                                                <input type="text" name="points" class="form-control" />
                                            </td>
                                         
                                <input type="hidden" name="marks_type" class="form-control" value="MARKS">
                                <input type="hidden" name="report_card_status" class="form-control" value="Y">
                                            
                                          
                                            <td>
                                                <input type="text" name="sort_order" class="form-control" />
                                            </td>
                                            <td>
                                                <input type="text" name="exam_date" class="form-control mydatepicker" autocomplete="off" />
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
                            </div>


                            <div class="col-md-12 form-group">
                                <center>
                                    <input type="submit" name="submit" value="Save" class="btn btn-success" >
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
        ["#standard", "#division", "#subject", "#exam", "#co_id"].forEach(field => {
            $(field).val("").empty().append('<option value="">Select</option>');
        });
    });

    $('#grade').change(function () {
        ["#subject", "#exam", "#co_id"].forEach(field => {
            $(field).empty().append('<option value="">Select</option>');
        });
    });
    $('#standard').change(function () {
        $("#exam").empty();
        $('#co_id').empty();
        $("#exam").append('<option value="">Select</option>');
        var standardID = $("#standard").val();
        var divisionID = $("#division").val();
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
    $('#standard').on('change', function () {
        var standardID = $("#standard").val();
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
        var grade = $("#grade").val();
        var standard = $("#standard").val();
        var subject = $("#subject").val();

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
                            $("#co_id").append('<option value="' + value.id + '">' + value.title + '</option>');
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
    })

</script>
<script>
    $(document).ready(function () {
        var counter = 0;

        $(".addrow").on("click", function () {
            var newRow = $("<tr>");
            var cols = "";

            cols += '<td><input type="text" name="title[]" class="form-control" /></td>';
            cols += '<td><input type="text" name="points[]" class="form-control" /></td>';
            cols += '<td> <select name="marks_type[]" class="form-control"><option value="">Select</option><option value="CBSE">MARKS</option><option value="GSEB">GRADE</option></select></td>';
            cols += '<td><select name="report_card_status[]" class="form-control"><option value="">Select</option><option value="Y">Yes</option><option value="N">No</option></select></td>';

            cols += '<td><input type="text" name="sort_order[]" class="form-control" /></td>';
            cols += '<td><input type="text" name="exam_date[]" class="form-control" /></td>';

            cols += '<td><input type="button" class="ibtnDel btn btn-md btn-danger "  value="-"></td>';
            newRow.append(cols);
            $("table.order-list").append(newRow);
            counter++;
        });



        $("table.order-list").on("click", ".ibtnDel", function (event) {
            $(this).closest("tr").remove();
            counter -= 1
        });


    });

</script>
@include('includes.footer')
