@extends('layout')
@section('container')

    <div id="page-wrapper">
        <div class="container-fluid">

            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title">Send SMS</h4>
                </div>
            </div>
        </div>

        <div class="card">
            <form name="sendsms" id="sendsms" action="{{ route('sendSMSCommon.store') }}" method="POST">
                @csrf
                @if (isset($data['studentData']))
                    @foreach ($data['studentData'] as $stuId => $students)
                        @foreach ($students as $value)
                            <input type="hidden" name="student[]" value="{{ $stuId }}">
                            <input type="hidden" name="student_mobile[]" value="{{ $value['student_mobile'] ?? '' }}">
                            <input type="hidden" name="parent_mobile[]" value="{{ $value['mobile'] ?? '' }}">
                            <input type="hidden" name="SMS_NAME[]"
                                value="{{ trim($value['first_name'] . ' ' . $value['middle_name'] . ' ' . $value['last_name']) }}">
                            <input type="hidden" name="SMS_PNAME[]" value="{{ $value['father_name'] ?? '' }}">
                        @endforeach
                    @endforeach
                @endif
                <div style="padding-top:10px; text-align:center;">
                    {{-- PopTable Header --}}

                    <table cellpadding="3" width="50%" style="margin: 0 auto;">
                        <tr>
                            <td align="left">
                                <select class="form-control" style="width:354px;" name="smsnumber"
                                    onchange="
                                    if(this.value=='mobile') {
                                        document.getElementById('st_lable').style.display='block';
                                        document.getElementById('pr_lable').style.display='none';
                                    } else {
                                        document.getElementById('st_lable').style.display='none';
                                        document.getElementById('pr_lable').style.display='block';
                                    }">
                                    <option value="parent_mobile_no">Parent</option>
                                    <option value="mobile">Student</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td align="left">
                                <label>
                                    <input type="checkbox" name="chkClickName" id="chkClickName" value="1"
                                        onclick="checkboxClickHandler(this);">
                                    <b>Unicode</b>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <td align="left">
                                <div id="translControl" style="display:none;"></div>
                            </td>
                        </tr>

                        <tr>
                            <td align="left">
                                <textarea name="DESC" id="transliterateDiv" class="cell_floating resizable" cols="48" rows="5"
                                    onkeyup="inputcharCounter(this);" required></textarea>
                            </td>
                        </tr>

                        <tr>
                            <td align="center" style="display:flex;">
                                <input type="text" name="MSGCNT" id="MSGCNT" class="cell_floating" size="5"
                                       readonly style="width:50px;">
                                <input type="text" name="LIMIT" class="cell_floating" size="5"
                                         readonly style="width:50px;">
                            </td>
                        </tr>

                        <tr>
                            <td align="left">
                                <label id="st_lable" style="display:none;">[STUDENT_NAME]</label>
                                <label id="pr_lable" style="display:block;">[PARENT_NAME]</label>
                            </td>
                        </tr>

                        <tr>
                            <td align="left">
                                <button type="submit" class="btn btn-success">Send SMS</button>
                            </td>
                        </tr>
                    </table>

                    {{-- PopTable Footer --}}
                </div>
            </form>
        </div>

    </div>
    </div>
    @include('includes.footerJs')

    <script type="text/javascript">
        function inputcharCounter(el) {
            const len = el.value.length;
            document.getElementById('MSGCNT').value = len;
            document.sendsms.LIMIT.value = len > 0 ? 1 : 0;
        }
        </script>
        @include('includes.footer')

    @endsection
