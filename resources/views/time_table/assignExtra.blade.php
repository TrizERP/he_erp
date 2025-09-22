@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Assign Extra Lecture</h4>
            </div>
        </div>
        @php
            $grade_id = $standard_id = $division_id = $dep_id = $emp_id = $search_dept = $search_emp = $from_date = $to_date =
                '';

            if (isset($data['grade_id'])) {
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if (isset($data['department_id'])) {
                $dep_id = $data['department_id'];
            }

            if (isset($data['selected_emp'])) {
                $emp_id = $data['selected_emp'];
            }

            if (isset($data['search_dept'])) {
                $search_dept = $data['search_dept'];
            }

            if (isset($data['search_emp'])) {
                $search_emp = $data['search_emp'];
            }

            if (isset($data['from_date'])) {
                $from_date = $data['from_date'];
            }

            if (isset($data['to_date'])) {
                $to_date = $data['to_date'];
            }
            // echo "<pre>";print_r($search_dept);print_r($search_emp);
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
        <h5>Assign Extra Lectures : </h5>
        <hr>
        <form action="{{ route('assign-extra-lecture.store') }}" class="row" method="post">
            @csrf
            <div class="col-md-4">
                <label for="">Date</label>
                <input type="text" name="extra_date" id="extra_date" placeholder="dd-mm-yyyy"
                    class="form-control mydatepicker" autocomplete="off">
            </div>
            {!! App\Helpers\HrmsDepartments('4', '', $dep_id, '', $emp_id, '') !!}
            {{ App\Helpers\SearchChain('4', 'single', 'grade,std,div', $grade_id, $standard_id, $division_id) }}
            <div class="col-md-4">
                <label for="">Type</label>
                <select name="lecture_type" id="lecture_type" class="form-control">
                    @foreach ($data['lecture_types'] as $key => $val)
                        <option value="{{ $val }}">{{ $val }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="">Batch</label>
                <select name="batch" id="batch" class="form-control">
                    <option value="">Select Batch</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="">Extra No.</label>
                <select name="lecture_no" id="lecture_no" class="form-control">
                    @foreach ($data['extra_nos'] as $key => $val)
                        <option value="{{ $val }}">{{ $val }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 mt-2">
                <center>
                    <input type="submit" value="Submit" class="btn btn-success">
                </center>
            </div>
        </form>
    </div>

    <div class="card">
        <h5>Search Extra Lectures : </h5>
        <hr>
        <form action="{{ route('assign-extra-lecture.index') }}" class="row">
            <div class="col-md-3">
                <label for="">From Date</label>
                <input type="text" name="from_date" id="from_date" placeholder="dd-mm-yyyy"
                    class="form-control mydatepicker" autocomplete="off"
                    @if (isset($from_date) && $from_date != '') value="{{ $from_date }}" @endif>
            </div>
            <div class="col-md-3">
                <label for="">To Date</label>
                <input type="text" name="to_date" id="to_date" placeholder="dd-mm-yyyy"
                    class="form-control mydatepicker" autocomplete="off"
                    @if (isset($to_date) && $to_date != '') value="{{ $to_date }}" @endif>
            </div>
            <div class="col-md-3 form-group">
                <label>Select Branch</label>
                <select class="form-control" name="search_dept" id="search_dept">
                    <option value="">Select Branch</option>
                    @foreach ($data['all_departments'] as $key => $val)
                        <option value="{{ $val->id }}" @if (isset($search_dept) && $search_dept == $val->id) selected @endif>
                            {{ $val->department }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 form-group">
                <label>Select Employee</label>
                <select name="search_emp" id="search_emp" class="form-control">

                </select>
            </div>

            <div class="col-md-12">
                <center>
                    <input type="submit" value="Search" name="search" class="btn btn-success">
                </center>
            </div>
        </form>
        @isset($data['allData'])
            <div class="row">
                <div class="col-md-12 form-group" id="printPage">
                    <table id="example" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sr No.</th>
                                <th>Date</th>
                                <th>Teacher</th>
                                <th>Standard</th>
                                <th>Division</th>
                                <th>Batch</th>
                                <th>Type</th>
                                <th>Extra No.</th>
                                <th class="text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['allData'] as $key => $val)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ date('d-m-Y', strtotime($val->extra_date)) }}</td>
                                    <td>{{ $val->emp_name }}</td>
                                    <td>{{ App\Helpers\getDataWithId($val->standard_id,"","standard","name") }}</td>
                                    <td>{{ App\Helpers\getDataWithId($val->section_id,"","division","name") }}</td>
                                    <td>{{ isset($val->batch_id) ?App\Helpers\getDataWithId($val->batch_id,"","batch","title") : '-' }}</td>
                                    <td>{{ $val->type }}</td>
                                    <td>{{ $val->lecture_no }}</td>
                                    <td>
                                        <form action="{{ route('assign-extra-lecture.destroy', $val->id) }}" method="POST" style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this?')">
                                                <span class="mdi mdi-delete"></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endisset
    </div>

</div>
</div>
@include('includes.footerJs')

<script>
    $(document).ready(function() {
        @if (isset($data['search_dept']) &&
                isset($data['search_emp']) &&
                $data['search_dept'] != '' &&
                $data['search_emp'] != '')
            getEmp({{ $data['search_dept'] }}, {{ $data['search_emp'] }});
        @endif

        $('#division').on('change', function() {
            $division = $(this).val();
            $standard = $('#standard').val();

            $.ajax({
                url: "{{ route('getBatchTimetable') }}",
                type: "GET",
                data: {
                    standard_id: $standard,
                    division_id: $division,
                    type: 'divisionWise',
                },
                success: function(response) {
                    $('#batch').empty(); // Clear existing options
                    $('#batch').append(`<option value="">Select Batch</option>`);
                    response.forEach(element => {
                        $('#batch').append(
                            `<option value="${element.id}">${element.title}</option>`
                            );
                    });
                }
            })
        })

        $('#search_dept').on('change', function() {
            let department = $(this).val();
            getEmp(department);
        })
        // Setup - add a text input to each footer cell    

        var table = $('#example').DataTable({
            select: true,
            lengthMenu: [
                [100, 500, 1000, -1],
                ['100', '500', '1000', 'Show All']
            ],
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'pdfHtml5',
                    title: 'Extra Lectures',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    pageSize: 'A0',
                    exportOptions: {
                        columns: ':visible'
                    },
                },
                {
                    extend: 'csv',
                    text: ' CSV',
                    title: 'Extra Lectures'
                },
                {
                    extend: 'excel',
                    text: ' EXCEL',
                    title: 'Extra Lectures'
                },
                {
                    extend: 'print',
                    text: ' PRINT',
                    title: 'Extra Lectures',
                    customize: function(win) {
                        $(win.document.body).prepend(`{!! App\Helpers\get_school_details("$grade_id", "$standard_id", "$division_id") !!}`);
                    }
                },
                'pageLength'
            ],
        });
        //table.buttons().container().appendTo('#example_wrapper .col-md-6:eq(0)');


        $('#example thead tr').clone(true).appendTo('#example thead');
        $('#example thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" placeholder="Search ' + title + '" />');

            $('input', this).on('keyup change', function() {
                if (table.column(i).search() !== this.value) {
                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

    });

    function getEmp(department, emp_id = '') {
        $.ajax({
            url: "{{ route('departmentwise-emplist') }}",
            type: "GET",
            data: {
                department_id: department,
            },
            success: function(response) {
                $('#search_emp').empty();
                $('#search_emp').append(`<option value="">Select Employee</option>`);
                response.forEach(element => {
                    let selected = '';
                    if (emp_id == element.id) {
                        selected = 'selected';
                    }
                    $('#search_emp').append(
                        `<option value="${element.id}" ${selected}>${element.full_name}</option>`
                        );
                });
            }
        })
    }
</script>
@include('includes.footer')
