@include('../includes.headcss')
@include('../includes.header')
@include('../includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="card">
            @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>
            </div>
            @endif
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    @if(isset($data['stu_data']) && count($data['stu_data']) > 0)
                    <form action="{{ route('send_sms_staff.store') }}" method="post">
                        @csrf
                        <input type="hidden" name="group_id" value="{{ $data['group_id'] }}">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>SMS</label>
                                <select name="smsText" id="smsText" class="form-control">
                                    <option value="">-- Select SMS Remark --</option>
                                    @if(isset($data['sms_remarks']))
                                        @foreach($data['sms_remarks'] as $remark)
                                            <option value="{{ $remark->title }}">{{ $remark->title }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 form-group">
                                <label>SMS Text</label>
                                <textarea required name="sms_content" id="smsContent" class="form-control" rows="4" placeholder="Enter SMS content..."></textarea>
                            </div>
                        </div>
                        <div class="table-responsive">                      
                            <table class="table-bordered table" id="myTable" width="100%">
                                <tr>
                                    <th><input type="checkbox" name="all" id="ckbCheckAll" class="ckbox"></th>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Branch</th>
                                    <th>Profile</th>
                                    <th>Mobile</th>
                                </tr>
                                @foreach($data['stu_data'] as $id => $col_arr)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="sendsms[{{ $col_arr['mobile'] ?? '' }}]" value="1" class="ckbox1">  
                                    </td>
                                    <td>{{ $id + 1 }}</td>
                                    <td>{{ $col_arr['name'] ?? 'N/A' }}</td>
                                    <td>{{ $col_arr['branch'] ?? 'N/A' }}</td>
                                    <td>{{ $col_arr['profile'] ?? 'N/A' }}</td>
                                    <td>{{ $col_arr['mobile'] ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                        <div class="row">    
                            <div class="col-md-12 form-group">
                                <center>
                                    <input type="submit" name="submit" value="Send SMS" class="btn btn-success">
                                </center>
                            </div>
                        </div>    
                    </form>
                    @else
                        <div class="row">                            
                            <div class="col-md-12 form-group">
                                <center>
                                    <span>No Record Found</span>
                                </center>
                            </div>
                        </div>
                    @endif
                </div>
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
    $(function () {
        var $tblChkBox = $("input:checkbox");
        $("#ckbCheckAll").on("click", function () {
            $($tblChkBox).prop('checked', $(this).prop('checked'));
        });

        // Auto-fill SMS text when dropdown selection changes
        $('#smsText').on('change', function() {
            var selectedText = $(this).val();
            $('#smsContent').val(selectedText);
        });
    });
</script>
@include('includes.footer')