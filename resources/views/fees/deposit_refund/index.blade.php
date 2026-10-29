@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <h4 class="page-title">Deposit Refund - Search Student</h4>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 text-right">
                <a href="{{ route('deposit_refund_my_requests.index') }}" class="btn btn-info">My Requests</a>
            </div>
        </div>

        @php
            $grade_id = $standard_id = $division_id = $enrollment_no = '';
            if (isset($data['grade_id'])) {
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if (isset($data['enrollment_no'])) {
                $enrollment_no = $data['enrollment_no'];
            }
        @endphp

        <div class="card">
            @if ($sessionData = Session::get('data'))
                @if($sessionData['status_code'] == 1)
                    <div class="alert alert-success alert-block">
                @else
                    <div class="alert alert-danger alert-block">
                @endif
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>{{ $sessionData['message'] }}</strong>
                    </div>
            @endif

            <form action="{{ route('deposit_refund_search') }}" method="post">
                @csrf
                <div class="row">
                    {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}
                    <div class="col-md-4 form-group">
                        <label>Enrollment No</label>
                        <input type="text" name="enrollment_no" value="{{ $enrollment_no }}" class="form-control">
                    </div>
                    <div class="col-md-12 form-group">
                        <center>
                            <input type="submit" value="Search" class="btn btn-success">
                        </center>
                    </div>
                </div>
            </form>
        </div>

        @if(isset($data['student_data']))
            <div class="card">
                <div class="table-responsive">
                    <table id="example" class="table table-striped">
                        <thead>
                        <tr>
                            <th>Sr.No</th>
                            <th>Student Name</th>
                            <th>Gr.No.</th>
                            <th>Standard</th>
                            <th>Division</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data['student_data'] as $key => $value)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $value['student_name'] }}</td>
                                <td>{{ $value['enrollment_no'] }}</td>
                                <td>{{ $value['standard_name'] }}</td>
                                <td>{{ $value['division_name'] }}</td>
                                <td>
                                    <a href="{{ route('deposit_refund_account', $value['id']) }}" class="btn btn-sm btn-primary">
                                        View Deposit Account
                                    </a>
                                    @if(isset($value['is_legacy']) && $value['is_legacy'])
                                        <span class="badge badge-warning">Legacy</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @include('includes.footerJs')
    <script>
        $(document).ready(function () {
            $('#example').DataTable();
        });
    </script>
    @include('includes.footer')
</div>
