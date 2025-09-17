@include('includes.headcss')
<style>
    table tr th{
        background-color: #c9c9c9;
        border: 2px solid #222528 !important;
        width: fit-content !important;
    }
    table tr td{
        border: 2px solid #222528 !important;
        background-color: #f9f9f9;
    }
</style>
<center>
    <button class="btn btn-primary"  onclick="PrintDiv('PrintDiv')">Print</button>
</center>
<div class="card" style="margin:15px 15px !important" id="PrintDiv">
    <div class="row">
        <div class="col-md-12">
            <!-- headnigs and student image  -->
            <center>
                <h2 style="text-decoration:underline;">{{ strtoupper(session()->get('school_name'))}}</h2>
                <br>
                <h2 style="text-decoration:underline;">STUDENT PROFILE</h2>
                <div class="imgDiv" style="padding:20px 0px;">
                    @if(isset($data['student_image']) && $data['student_image']!='')
                    <img src="{{asset('/storage/student/'.$data['student_image'])}}" alt="{{$data['student_image']}}" width="150" height="150">
                    @else 
                    <img src="{{asset('admin_dep/images/no-student.jpg')}}" alt="no-Img" width="150" height="150">
                    @endif
                </div>
            </center>
            <!-- headnigs and student image  -->
             <!-- enrollment and rollno -->
            <div class="student-number" style="display:flex;flex-wrap:wrap;justify-content: space-around;margin: 24px 0px;">
                <div class="enroll">
                    <h4><b>Enrollment No. :</b>{{$data['enrollment_no']}}</h4>
                </div>
            <!--
                <div class="roll">
                    <h4><b>Roll No. :</b>{{$data['roll_no']}}</h4>
                </div>
            -->
            </div>
             <!-- enrollment and rollno end -->
            <!-- personal detail table start  -->
            <div class="personalData" style="padding:20px 30px;">
                <h3><b>PERSONAL DETAIL</b></h3>
                <table class="table table-bordered" width="100%">
                    @foreach($data['personalData'] as $key => $value)
                    <tr>
                        <th><b>{{$key}}<b></th>
                        <td><b>{{$value}}<b></td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <!-- personal detail table end  -->
            <!-- parents detail table start  -->
            <div class="personalData" style="padding:20px 30px;">
                <h3><b>PARENTS INFORMATION</b></h3>
                <table class="table table-bordered" width="100%">
                    @foreach($data['parentData'] as $key => $value)
                    <tr>
                        <th><b>{{$key}}<b></th>
                        <td><b>{{$value}}<b></td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <!-- Parent detail table end  -->
            <!-- academic detail table start  -->
            <div class="personalData" style="padding:20px 30px;">
                <h3><b>ACADEMIC DETAIL</b></h3>
                <table class="table table-bordered" width="100%">
                  <tr>
                        <th><b>Academic Years :</b></th>
                        <td><b>{{isset($data['academicData']['academic_section']) ? $data['academicData']['academic_section'] : 'N/A'}}</b></td>
                        <th><b>Branch :</b></th>
                        <td><b>{{isset($data['academicData']['branch']) ? $data['academicData']['branch'] : 'N/A'}}</b></td>
                  </tr>
                  <tr>
                        <th><b>Division :</b></th>
                        <td><b>{{isset($data['academicData']['division']) ? $data['academicData']['division'] : 'N/A'}}</b></td>
                        <th><b>Student Quota :</b> </th>
                        <td><b>{{isset($data['academicData']['student_quota']) ? $data['academicData']['student_quota'] : 'N/A'}}</b></td>
                  </tr>
                  <tr>
                    <th><b>Admission Year :</b></th>
                    <td colspan="3"><b>{{isset($data['academicData']['admission_year']) ? $data['academicData']['admission_year'] : 'N/A'}}</b></td>
                  </tr>
                </table>
            </div>
            <!-- academic detail table end  -->
            <!-- past education table start  -->
            @if(!empty($data['pastEducation']))
            <div class="personalData" style="padding:20px 30px;">
                <h3><b>PAST EDUCATION</b></h3>
                <table class="table table-bordered" width="100%">
                    <tr>
                        <th><b>Sr No.</b></th>
                        <th><b>Course</b></th>
                        <th><b>Medium</b></th>
                        <th><b>Name of Board</b></th>
                        <th><b>Year of Passing</b></th>
                        <th><b>Percentage</b></th>
                        <th><b>School Name</b></th>
                        <th><b>Place</b></th>
                        <th><b>Trail</b></th>
                    </tr>
                    @foreach($data['pastEducation'] as $key => $value)
                    <tr>
                        <td><b>{{$key+1}}<b></td>
                        <td><b>{{$value->course}}<b></td>
                        <td><b>{{$value->medium}}<b></td>
                        <td><b>{{$value->name_of_board}}<b></td>
                        <td><b>{{$value->year_of_passing}}<b></td>
                        <td><b>{{$value->percentage}}<b></td>
                        <td><b>{{$value->school_name}}<b></td>
                        <td><b>{{$value->place}}<b></td>
                        <td><b>{{$value->trial}}<b></td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif
            <!-- past education table end  -->
            <!-- Certificate table start  -->
            @if(!empty($data['issuedCertificates']))
            <div class="personalData" style="padding:20px 30px;">
                <h3><b>ISSUED CIRTIFICATE DETAILS</b></h3>
                <table class="table table-bordered" width="100%">
                    <tr>
                        <th><b>Sr No.</b></th>
                        <th><b>Semester</b></th>
                        <th><b>Certificate No.</b></th>
                        <th><b>Certificate Name</b></th>
                        <th><b>Date</b></th>
                        <th><b>Reason</b></th>
                    </tr>
                    @foreach($data['issuedCertificates'] as $key => $value)
                    <tr>
                        <td><b>{{$key+1}}<b></td>
                        <td><b>{{$value->stdName}}<b></td>
                        <td><b>{{$value->certificate_number}}<b></td>
                        <td><b>{{$value->certificate_type}}<b></td>
                        <td><b>{{ date('d/m/Y',strtotime($value->created_at))}}<b></td>
                        <td><b>-<b></td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif
            <!-- Certificate table end  -->
            <!-- fees collect table start  -->
            @if(!empty($data['feesDetails']))
            <div class="personalData" style="padding:20px 30px;">
                <h3><b>FEES DETAIL</b></h3>
                <table class="table table-bordered" width="100%">
                    <tr>
                        <th><b>Sr No.</b></th>
                        <th><b>Semester</b></th>
                        <th><b>Rec. No.</b></th>
                        <th><b>Date</b></th>
                        <th><b>Payment Mode</b></th>
                        <th><b>Cheque/DD No.</b></th>
                        <th><b>Bank Name</b></th>
                        <th><b>Bank Branch</b></th>
                        <th><b>Amount</b></th>
                        <th><b>Received By</b></th>
                    </tr>
                    @foreach($data['feesDetails'] as $key => $value)
                    <tr>
                        <td><b>{{$key+1}}<b></td>
                        <td><b>{{$value->stdName}}<b></td>
                        <td><b>{{$value->receipt_no}}<b></td>
                        <td><b>{{ date('d/m/Y',strtotime($value->receiptdate)) }}<b></td>
                        <td><b>{{$value->payment_mode}}<b></td>
                        <td><b>{{$value->cheque_no}}<b></td>
                        <td><b>{{$value->cheque_bank_name}}<b></td>
                        <td><b>{{$value->bank_branch}}<b></td>
                        <td><b>{{$value->fees_paid}}<b></td>
                        <td><b>{{$value->received_by}}<b></td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif
            <!-- fees collect table end  -->
             <!-- fees collect table start  -->
            @if(!empty($data['cancelFeesCollect']))
             <div class="personalData" style="padding:20px 30px;">
                <h3><b>CANCEL FEES DETAIL</b></h3>
                <table class="table table-bordered" width="100%">
                    <tr>
                        <th><b>Sr No.</b></th>
                        <th><b>Rec. No.</b></th>		
                        <th><b>Amount</b></th>		
                        <th><b>Semester</b></th>
                        <th><b>Payment Mode</b></th>
                        <th><b>Cheque/DD No.</b></th>
                        <th><b>Bank Name</b></th>
                        <th><b>Bank Branch</b></th>
                        <th><b>Cancellation Type</b></th>
                        <th><b>Cancellation Remarks</b></th>
                        <th><b>Cancelled By</b></th>
                        <th><b>Cancelled Date</b></th>
                    </tr>
                    @foreach($data['cancelFeesCollect'] as $key => $value)
                    <tr>
                        <td><b>{{$key+1}}<b></td>
                        <td><b>{{$value->reciept_id}}<b></td>
                        <td><b>{{$value->amountpaid}}<b></td>
                        <td><b>{{$value->std_name}}<b></td>
                        <td><b>{{$value->payment_mode}}<b></td>
                        <td><b>{{$value->cheque_no}}<b></td>
                        <td><b>{{$value->cheque_bank_name}}<b></td>
                        <td><b>{{$value->bank_branch}}<b></td>
                        <td><b>{{$value->cancel_type}}<b></td>
                        <td><b>{{$value->cancel_remark}}<b></td>
                        <td><b>{{$value->cancelled_by}}<b></td>
                        <td><b>{{ date('d/m/Y',strtotime($value->cancel_date)) }}<b></td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif
            <!-- fees collect table end  -->
            <!-- Other fees collect table start  -->
            @if(!empty($data['otherFeesDetails']))
            <div class="personalData" style="padding:20px 30px;">
                <h3><b>OTHER FEES DETAILS</b></h3>
                <table class="table table-bordered" width="100%">
                    <tr>
                        <th><b>Sr No.</b></th>
                        <th><b>Semester</b></th>
                        <th><b>Head</b></th>
                        <th><b>Rec. No.</b></th>
                        <th><b>Date</b></th>
                        <th><b>Amount</b></th>
                        <th><b>Remarks</b></th>
                        <th><b>Received By</b></th>
                    </tr>
                    @foreach($data['otherFeesDetails'] as $key => $value)
                    <tr>
                        <td><b>{{$key+1}}<b></td>
                        <td><b>{{$value->stdName}}<b></td>
                        <td><b>{{$value->fees_head}}<b></td>
                        <td><b>{{$value->receipt_id}}<b></td>
                        <td><b>{{date('d/m/Y',strtotime($value->deduction_date))}}<b></td>
                        <td><b>{{$value->deduction_amount}}<b></td>
                        <td><b>-<b></td>
                        <td><b>{{$value->received_by}}<b></td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif
            <!-- Other fees collect table end  -->
        </div>
    </div>
</div>
<div class="bottomDiv" style="margin-bottom:100px">
<center>
    <button class="btn btn-primary" onclick="PrintDiv('PrintDiv')">Print</button>
</center>
</div>

@include('includes.footerJs')
<script>
function PrintDiv(divName){
    var divToPrint = document.getElementById(divName);
    var popupWin = window.open('', '_blank', 'width=800,height=600');
    popupWin.document.open();
    popupWin.document.write('<html><head><title>Print</title>');
    
    // Include the CSS file
    popupWin.document.write('<link rel="stylesheet" href="/css/style.css" />');
    
    // Add print-specific styles
    popupWin.document.write(`
        <style>
            @media print {
                table {
                    border-collapse: collapse;
                    width: 100%;
                }
                table tr th, table tr td {
                    border: 2px solid #222528 !important;
                    padding: 8px;
                }
                table tr th {
                    background-color: #c9c9c9;
                }
                table tr td {
                    background-color: #f9f9f9;
                }
                img {
                    width: 200px;
                    height: 200px;
                    object-fit: cover; /* Ensure proper image scaling */
                }
            }
        </style>
    `);
    
    popupWin.document.write('</head><body onload="window.print()">');
    popupWin.document.write(divToPrint.innerHTML);
    popupWin.document.write('</body></html>');
    popupWin.document.close();
}

</script>
@include('includes.footer')