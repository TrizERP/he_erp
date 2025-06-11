@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
      rel="stylesheet">
<style>
    .toggle.btn.btn-danger {
        width: 200px !important;
    }

    .toggle.btn.btn-warning {
        width: 200px !important;
    }
</style>
<div id="page-wrapper">
    <div class="container-fluid">
    <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">Fees Cancel</h4> </div>
            </div>
        <div class="row bg-title">
            <div class="col-md-3 d-flex">
                <input type="checkbox" id="toggle_cancel_refund" name="toggle_cancel_refund" checked
                       data-toggle="toggle" data-on="Fees Cancel" data-off="Fees Refund" data-onstyle="warning"
                       data-offstyle="danger" onchange="show_fees_cancel_refund();">
            <!-- <h4 class="page-title">
                    <a href="{{ route("fees_cancel.index") }}" class="btn btn-info add-new">Fees Cancel</a>
                </h4>

                <h4 class="page-title ml-2">
                    <a href="{{ route("add_requisition.create") }}" class="btn btn-info add-new">Fees Refund</a>
                </h4> -->
            </div>
        </div>
        @php
            $grade_id = $standard_id = $division_id = $enrollment_no = $receipt_no = $from_date = $to_date = '';

            if(isset($data['grade_id'])){
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            if(isset($data['enrollment_no']))
            {
                $enrollment_no = $data['enrollment_no'];
            }
            if(isset($data['receipt_no']))
            {
                $receipt_no = $data['receipt_no'];
            }
            if(isset($data['from_date']))
            {
                $from_date = $data['from_date'];
            }
            if(isset($data['to_date']))
            {
                $to_date = $data['to_date'];
            }
        @endphp
        <div class="card"> <!--  py-0 -->
            @if ($sessionData = Session::get('data'))
                @if($sessionData['status_code'] == 1)
                    <div class="alert alert-success alert-block">
                        @else
                            <div class="alert alert-danger alert-block">
                                @endif
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>{{ $sessionData['message'] }}</strong>
                            </div>
                        @endif
                        <form action="{{ route('show_cancel_fees') }}" enctype="multipart/form-data" method="post">
                            {{ method_field("POST") }}
                            @csrf
                            <div class="row">

                                {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}
                                <div class="col-md-4 form-group">
                                    <label>{{ App\Helpers\get_string('grno','request')}}</label>
                                    <input type="text" id="enrollment_no" name="enrollment_no"
                                           value="{{$enrollment_no}}" class="form-control">
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>From Date</label>
                                    <input type="text" id="from_date" name="from_date" value="{{$from_date}}"
                                           class="form-control mydatepicker" autocomplete="off">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>To Date</label>
                                    <input type="text" id="to_date" name="to_date" value="{{$to_date}}"
                                           class="form-control mydatepicker" autocomplete="off">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Receipt No</label>
                                    <input type="text" id="receipt_no" value="{{$receipt_no}}" name="receipt_no"
                                           class="form-control">
                                </div>

                                <div class="col-md-12 form-group">
                                    <center>
                                        <input type="submit" name="submit" value="Search" class="btn btn-success">
                                    </center>
                                </div>

                            </div>
                        </form>
                    </div>
        </div>
        @if(isset($data['fees_data']))
            @php
                if(isset($data['fees_data'])){
                    $fees_data = $data['fees_data'];
                }
            @endphp
            <div class="card">
                <form method="POST" action="cancel_fees">
                    <div class="row">
                        <div class="col-lg-12 col-sm-12 col-xs-12 p-0">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th><input id="checkall" onchange="checkAll(this);" type="checkbox"></th>
                                        <th>{{ App\Helpers\get_string('grno','request')}}</th>
                                        <th>{{ App\Helpers\get_string('studentname','request')}}</th>
                                        <th>{{ App\Helpers\get_string('standard','request')}}</th>
                                        <th>{{ App\Helpers\get_string('division','request')}}</th>
                                        <th>Receipt No</th>
                                        <th>Amount</th>
                                        <th>Receipt Date</th>
                                        <th>Created Date</th>
                                        <th>Payment Mode</th>
                                        <th>Cancel Type</th>
                                        <th>Cancel Remarks</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $j=1;
                                    @endphp
                                    @if(isset($data['fees_data']))
                                        @foreach($fees_data as $key => $value)
                                           
                                            <tr>
                                                <td><input id="{{$value['id']}}" value="{{$value['receipt_no']}}####{{$value['student_id']}}"
                                                               name="receipt_no[]" type="checkbox"></td>
                                             
                                                <td>{{$value['enrollment_no']}}</td>
                                                <td>{{$value['student_name']}}</td>
                                                <td>{{$value['standard_name']}}</td>
                                                <td>{{$value['division_name']}}</td>
                                                <td>
                                                    <button type="button" class="btn btn-info float-right"
                                                            data-toggle="modal"
                                                            onclick="javascript:add_data({{$value['id']}},{{$value['student_id']}},'{{$value['receipt_no']}}');">{{$value['receipt_no']}}</button>
                                                    <input type="hidden" name="fees_html_{{$value['id']}}"
                                                           id="fees_html_{{$value['id']}}"
                                                           value="{{$value['fees_html']}}">
                                                    <input type="hidden" name="student_id[{{$value['student_id']}}]"
                                                           value="{{$value['student_id']}}">
                                                </td>
                                                <td><input type="hidden" name="totAmt[{{$value['receipt_no']}}]"
                                                           value="{{$value['total_amount']}}">
                                                    <input type="hidden" name="month_id"
                                                           value="{{$value['month_id']}}">{{$value['total_amount']}}</td>
                                                           
                                                <td>{{$value['receiptdate']}}</td>
                                                <td>{{$value['created_on']}}</td>
                                                <td>{{$value['payment_mode']}}</td>
                                                @if($value['fees_type'] == "REGULAR")
                                                    <td>
                                                        <select name="cancel_type[{{$value['receipt_no']}}/{{$value['student_id']}}]"
                                                                class="form-control">
                                                            <option value="">Select Cancel Type</option>
                                                            @foreach($data['fees_cancel_type'] as $fctId => $fctTitle)
                                                                <option value="{{$fctTitle}}/">{{$fctTitle}}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                               name="cancel_remark[{{$value['receipt_no']}}/{{$value['student_id']}}]"
                                                               class="form-control"
                                                               placeholder="Please enter cancel remark">
                                                    </td>
                                                @else
                                                    <td>-</td>
                                                    <td>-</td>
                                                @endif
                                            </tr>
                                            @php
                                                $j++;
                                            @endphp
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-12 form-group mt-4">
                                <center>
                                    <input type="submit" name="submit" value="Cancel" class="btn btn-success">
                                </center>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <!--Modal: Add ChapterModal-->
    <div id="printThis">
        <div class="modal fade right modal-scrolling" id="ChapterModal" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-side modal-bottom-right modal-notify modal-info" role="document"
                 style="min-width: 75%;">
                <!--Content-->
                <div class="modal-content">
                    <!--Header-->
                    <div class="modal-header">
                        <h5 class="modal-title" id="heading">Re-Print Receipt</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">x</span>
                        </button>
                    </div>
                    <!--Body-->
                    <div class="modal-body">
                        <div class="row">
                            <div class="panel-body">
                                <div class="col-lg-12 col-sm-12 col-xs-12">
                                    <input type="hidden" name="action" id="action" value="fees_re_receipt">
                                    <input type="hidden" name="student_id" id="student_id" value="">
                                    <input type="hidden" name="receipt_id_html" id="receipt_id_html" value="">
                                    <input type="hidden" name="paper_size" id="paper_size"
                                           value="{{$data['paper_size']}}">
                                    <div id="reprint_receipt_html">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Footer-->
                    <div class="modal-footer" style="display: block !important;">
                        <div id="overlay" style="display:none;">
                            <center><p style="margin-top: 273px;color:red;font-weight: 700;">Please do not refresh the
                                    page, while the process is going on.</p><img
                                    src="http://dev.triz.co.in/admin_dep/images/loader.gif"></center>
                        </div>
                        <center>
                            <button id="ajax_PDF" type="button" class="btn btn-primary">Print Receipt</button>
                        </center>
                    </div>
                </div>
                <!--/.Content-->
            </div>
        </div>
    </div>
    <!--Modal: Add ChapterModal-->


    @include('includes.footerJs')
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
    <script>
        // document.getElementById("btnPrint").onclick = function () {
        //     // alert('dddd');
        //         PrintDiv("reprint_receipt_html");
        // }

        // function PrintDiv(divName) {
        //     var divToPrint = document.getElementById(divName);
        //     var popupWin = window.open('', '_blank', 'width=300,height=300');
        //     popupWin.document.open();
        //     popupWin.document.write('<html>');
        //     popupWin.document.write('<body onload="window.print()">' + divToPrint.innerHTML + '</html>');
        //     popupWin.document.close();
        // }

        function add_data(fees_collect_id, student_id, receipt_no) {
            var css = "{{$data['receipt_css_data']}}";
            var recepit_css = "<style>" + css + "</style>";
            var fees_content = $('#fees_html_' + fees_collect_id).val();
            // alert(fees_content);
            $('#reprint_receipt_html').html(recepit_css + fees_content);
            $('#student_id').val(student_id);
            $('#receipt_id_html').val(receipt_no);
            $('#ChapterModal').modal('show');

        }

        function checkAll(ele) {
            var checkboxes = document.getElementsByTagName('input');
            if (ele.checked) {
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].type == 'checkbox') {
                        checkboxes[i].checked = true;
                    }
                }
            } else {
                for (var i = 0; i < checkboxes.length; i++) {
                    console.log(i)
                    if (checkboxes[i].type == 'checkbox') {
                        checkboxes[i].checked = false;
                    }
                }
            }
        }

        function show_fees_cancel_refund() {
            if ($("#toggle_cancel_refund").prop("checked") == true) {
                var path = "{{ route('fees_cancel.index') }}";
                location.href = path;
            } else {
                var path1 = "{{ route('fees_refund.index') }}";
                location.href = path1;
            }
        }
    </script>
    <script>
        $(document).ready(function () {

            $('#example').DataTable({
                "order": [
                    [1, 'asc']
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": 0
                }]
            });
        });
    </script>
    @include('includes.footer')
    <style type="text/css">
        @media screen {
            #printSection {
                display: none;
            }
        }
    </style>
