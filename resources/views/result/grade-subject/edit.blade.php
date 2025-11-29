@include('../includes.headcss')
@include('../includes.header')
@include('../includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">

        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Edit Grade Subject</h4>
            </div>
        </div>

        <div class="card">

            <div class="col-lg-12 col-sm-12 col-xs-12">

                <form action="{{ route('grade-subject.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- REQUIRED HIDDEN FIELDS --}}
                    <input type="hidden" name="syear" value="{{ session('syear') }}">
                    <input type="hidden" name="sub_institute_id" value="{{ session('sub_institute_id') }}">
                    <input type="hidden" name="term_id" id="term_id" value="{{ $item->term_id }}">

                    <div class="row">

                        {{-- TERM DROPDOWN --}}
                        {{ App\Helpers\TermDD($item->term_id) }}

                        {{-- GRADE / STANDARD DROPDOWN --}}
                        {{ App\Helpers\SearchChain('4','single','grade,std',$item->grade_id,$item->standard_id) }}

                        {{-- SUBJECT --}}
                        <div class="col-md-4 form-group">
                            <label>Select Subject:</label>
                            <select name="subject" id="subject" class="form-control" required>
                                <option value="">Select</option>

                                {{-- Prefill subject --}}
                                <option value="{{ $item->subject }}" selected>{{ $item->subject_name  }}</option>
                            </select>
                        </div>

                        {{-- TABLE --}}
                        <div class="col-md-12 form-group">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Breakoff</th>
                                            <th>Sort Order</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="text" name="title[]" class="form-control"
                                                    value="{{ $item->title }}">
                                            </td>
                                            <td>
                                                <input type="text" name="breakoff[]" class="form-control"
                                                    value="{{ $item->breakoff }}">
                                            </td>
                                            <td>
                                                <input type="text" name="sort_order[]" class="form-control"
                                                    value="{{ $item->sort_order }}">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <center>
                                <button type="submit" class="btn btn-success">Update</button>
                            </center>
                        </div>

                    </div>

                </form>

            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Fix the following errors:
                    <ul>
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>

    </div>
</div>

@include('includes.footerJs')
@include('includes.footer')

<script>
   const requiredFields = ["#grade", "#standard", "#division", "#subject", "#term", "#exam"];
    requiredFields.forEach(field => $(field).prop('required', true));

    $('#term').change(function () {
        ["#standard", "#division", "#subject", "#exam"].forEach(field => {
            $(field).val("").empty().append('<option value="">Select</option>');
        });
    });
    $('#term').change(function () {
        $("#term_id").val($(this).val());
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
