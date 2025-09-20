@include('../includes.headcss')
@include('../includes.header')
@include('../includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Send Sms Parents</h4>
            </div>
        </div>
        <div class="card">
            @if (Session::has('success'))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ Session::get('success') }}</strong>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    @if (isset($data['stu_data']))
                        <form action="{{ route('send_sms_parents.store') }}" enctype="multipart/form-data"
                            method="POST">
                            @csrf
                            @php
                                $numType = 'mobile';
                                if (!empty($data['number_type'])) {
                                    $numType = $data['number_type'];
                                }
                            @endphp

                            <input type="hidden" name="grade" value="{{ $data['grade'] }}">
                            <input type="hidden" name="standard" value="{{ $data['standard'] }}">
                            <input type="hidden" name="division" value="{{ $data['division'] }}">
                            <input type="hidden" name="number_type" value="{{ $data['number_type'] }}">

                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>SMS Text</label>
                                    <textarea required name="smsText" class="form-control"></textarea>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table-bordered table" id="myTable" width="100%">
                                    <tr>
                                        <th><input type="checkbox" name="all" id="ckbCheckAll" class="ckbox"></th>
                                        <th>No</th>
                                        <th>Enrollment No</th>
                                        <th>Student Name</th>
                                        <th>Standard</th>
                                        <th>Division</th>
                                        @if (!empty($data['number_type']))
                                            <th>{{ $data['number_types'][$data['number_type']] ?? '-' }}</th>
                                        @else
                                            @foreach ($data['number_types'] as $val)
                                                <th>{{ $val }}</th>
                                            @endforeach
                                        @endif
                                    </tr>

                                    @foreach ($data['stu_data'] as $id => $col_arr)
                                        @php
                                            $sendNumber = $col_arr['mobile'] ?? '';
                                            if (!empty($data['number_type'])) {
                                                $sendNumber = $col_arr[$data['number_type']];
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="sendsms[{{ $sendNumber }}]"
                                                    class="ckbox1">
                                            </td>
                                            <td>{{ $id + 1 }}</td>
                                            <td>{{ $col_arr['enrollment_no'] }}</td>
                                            <td>{{ $col_arr['name'] }}</td>
                                            <td>{{ $col_arr['standard_name'] }}</td>
                                            <td>{{ $col_arr['division_name'] }}</td>
                                            @if (!empty($data['number_type']))
                                                <td>{{ $col_arr[$data['number_type']] }}</td>
                                            @else
                                                @foreach ($data['number_types'] as $k=> $val)
                                                    <td>{{ $col_arr[$k] }}</td>
                                                @endforeach
                                            @endif
                                        </tr>
                                    @endforeach
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-md-12 form-group text-center">
                                    <input type="submit" name="submit" value="Save" class="btn btn-success">
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="row">
                            <div class="col-md-12 form-group text-center">
                                <span>No Record Found</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($errors->any())
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
    $(function() {
        var $tblChkBox = $("input:checkbox");
        $("#ckbCheckAll").on("click", function() {
            $($tblChkBox).prop('checked', $(this).prop('checked'));
        });
    });
</script>
@include('includes.footer')
