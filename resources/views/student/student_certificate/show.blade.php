{{--@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')--}}
@extends('layout')
@section('container')
<style type="text/css">
   #overlay {
       position: fixed; /* Sit on top of the page content */
       display: none; /* Hidden by default */
       width: 100%; /* Full width (cover the whole page) */
       height: 100%; /* Full height (cover the whole page) */
       top: 0;
       left: 0;
       right: 0;
       bottom: 0;
       background-color: rgba(0,0,0,0.5); /* Black background with opacity */
       z-index: 2; /* Specify a stack order in case you're using a different order for other elements */
       cursor: pointer; /* Add a pointer on hover */
   }
</style>
<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
         <h4 class="page-title">Student Certificate History</h4>
      </div>
   </div>
   @php
       $grade_id = $standard_id = $division_id = '';
       if(isset($data['grade_id'])){
           $grade_id = $data['grade_id'];
           $standard_id = $data['standard_id'];
           $division_id = $data['division_id'];
       }
       $from_date = $to_date = '';
       if(isset($data['from_date'])){
           $from_date = $data['from_date'];
       }
       if(isset($data['to_date'])){
           $to_date = $data['to_date'];
       }
   @endphp
   <div class="card">
      @if(!empty($data['message']))
         @if($data['status_code'] == 1)
             <div class="alert alert-success alert-block">
         @else
             <div class="alert alert-danger alert-block">
         @endif
             <button type="button" class="close" data-dismiss="alert">×</button>
             <strong>{!! $data['message'] !!}</strong>
         </div>
      @endif
      <form action="{{ route('student_certificate_report.create') }}" enctype="multipart/form-data">
         @csrf
         <div class="row">
             {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}
             <div class="col-md-4 form-group">
                <label>{{App\Helpers\get_string('studentname')}}<i class="mdi mdi-lead-pencil"></i></label>
                <input type="text" id="stu_name" placeholder="{{App\Helpers\get_string('studentname')}}" name="stu_name" class="form-control" @if(isset($data['stu_name'])) value="{{$data['stu_name']}}" @endif>
             </div>
             <div class="col-md-4 form-group">
                <label>{{App\Helpers\get_string('uniqueid')}}<i class="mdi mdi-lead-pencil"></i></label>
                <input type="text" id="uniqueid" placeholder="{{App\Helpers\get_string('uniqueid')}}" name="uniqueid" class="form-control" @if(isset($data['uniqueid'])) value="{{$data['uniqueid']}}" @endif>
             </div>
             <div class="col-md-4 form-group">
                <label>Mobile</label>
                <input type="text" id="mobile" placeholder="Mobile" name="mobile" class="form-control" @if(isset($data['mobile'])) value="{{$data['mobile']}}" @endif>
             </div>
             <div class="col-md-4 form-group">
                <label>{{App\Helpers\get_string('grno')}}<i class="mdi mdi-lead-pencil"></i></label>
                <input type="text" id="grno" placeholder="{{App\Helpers\get_string('grno')}}" name="grno" class="form-control" @if(isset($data['grno'])) value="{{$data['grno']}}" @endif>
             </div>
             <div class="col-md-4 form-group">
                <label>From Date</label>
                <input type="text" name="from_date" class="form-control mydatepicker"
                       placeholder="Please select from date." autocomplete="off" value="{{$from_date}}">
             </div>
             <div class="col-md-4 form-group">
                <label>To Date</label>
                <input type="text" name="to_date" class="form-control mydatepicker"
                       placeholder="Please select to date." autocomplete="off" value="{{$to_date}}">
             </div>
             <div class="col-md-4 form-group">
                 <label for="">Certificate Type</label>
                 <select name="certificate_type" id="certificate_type" class="form-control">
                     <option value="">Select Certificate Type</option>
                     @foreach($data['report_types'] as $key => $value)
                         <option value="{{$value->module_name}}" @if(isset($data['certificate_type']) && $data['certificate_type']==$value->module_name) selected @endif>{{$value->module_name}}</option>
                     @endforeach
                 </select>
             </div>
             <div class="col-md-12 form-group mt-4">
                 <center>
                     <input type="submit" name="submit" value="Search" class="btn btn-success">
                 </center>
             </div>
         </div>
      </form>
   </div>

   @if(isset($data['result_report']))
       @php
           $j = 1;
           $result_report = $data['result_report'];
       @endphp
       <div class="card">
           <div class="table-responsive">
              
               <br>
               {{-- Academic Year + From-To Dates --}}
                @php
                    echo App\Helpers\get_school_details($grade_id, $standard_id, $division_id);

                    $getInstitutes = session()->get('getInstitutes');
                    $academicYears = session()->get('academicYears');
                    $syear = session()->get('syear');


            $nextYear = $syear + 1;

                @endphp


        {{-- ✅ Academic Year Label (same font as address) --}}
           
                <center>
                    <span style="font-size: 15px; font-weight: 600; font-family: Arial, Helvetica, sans-serif !important; display:block; margin-top: 15px; margin-bottom: 5px;">
                        Academic Year : {{ $syear }} - {{ $nextYear }}
                    </span>
                </center>

                   <br>
                   <center>
                   <span style="font-size: 14px;font-weight: 600;font-family: Arial, Helvetica, sans-serif !important">
                       From Date : {{ isset($data['from_date']) ? date('d-m-Y',strtotime($data['from_date'])) : '-' }} -
                   </span>
                   <span style="font-size: 14px;font-weight: 600;font-family: Arial, Helvetica, sans-serif !important">
                       To Date : {{ isset($data['to_date']) ? date('d-m-Y',strtotime($data['to_date'])) : '-' }}
                   </span>
               </center>
               <br>
               <table id="example" class="table table-striped">
                   <thead>
                       <tr>
                           <th>SR NO</th>
                           <th>{{App\Helpers\get_string('grno','request')}}</th>
                           <th>{{App\Helpers\get_string('studentname','request')}}</th>
                           <th>{{App\Helpers\get_string('standard','request')}}</th>
                           <th>{{App\Helpers\get_string('division','request')}}</th>
                           <th>Certificate No.</th>
                           <th class="text-left">Certificate Type</th>
                           <th class="text-left">Created at</th>
                       </tr>
                   </thead>
                   <tbody>
                       @foreach($result_report as $key => $value)
                           @php
                               $first_name = $value['first_name'] ?? '-';
                               $middle_name = $value['middle_name'] ?? '-';
                               $last_name = $value['last_name'] ?? '-';
                               $student_name = $first_name.' '.$middle_name.' '.$last_name;
                           @endphp
                           <tr>
                               <td>{{$j++}}</td>
                               <td>{{$value['enrollment_no']}}</td>
                               <td>{{$student_name}}</td>
                               <td>{{$value['standard_name']}}</td>
                               <td>{{$value['division_name']}}</td>
                               <td>
                                   <button type="button" class="btn btn-info float-right" data-toggle="modal" onclick="add_data({{$value['certi_id']}},{{$value['student_id']}},{{$value['certificate_number']}});">
                                       {{$value['certificate_number']}}
                                   </button>
                                   <input type="hidden" name="certificate_html_{{$value['certi_id']}}" id="certificate_html_{{$value['certi_id']}}" value="{{$value['certificate_html']}}">
                               </td>
                               <td>{{$value['certificate_type']}}</td>
                               <td>{{$value['created_at']}}</td>
                           </tr>
                       @endforeach
                   </tbody>
               </table>
           </div>
       </div>
   @endif
</div>
</div>

<!-- Modal: Re-Print Certificate -->
<div id="printThis">
   <div class="modal fade right modal-scrolling" id="ChapterModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
       <div class="modal-dialog modal-side modal-bottom-right modal-notify modal-info" role="document" style="min-width: 75%;">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="heading">Re-Print Certificate</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">x</span>
                   </button>
               </div>
               <div class="modal-body">
                   <div class="row">
                       <div class="panel-body" style="width: 100%;">
                           <div class="col-md-12">
                               <input type="hidden" name="action" id="action" value="certificate_re_receipt">
                               <input type="hidden" name="student_id" id="student_id" value="">
                               <input type="hidden" name="receipt_id_html" id="receipt_id_html" value="">
                               <input type="hidden" name="paper_size" id="paper_size" value="">
                               <div id="reprint_certificate_html"></div>
                           </div>
                       </div>
                   </div>
               </div>
               <div class="modal-footer" style="display: block !important;">
                   <div id="overlay" style="display:none;">
                       <center>
                           <p style="margin-top: 273px;color:red;font-weight: 700;">Please do not refresh the page, while the process is going on.</p>
                           <img src="http://dev.triz.co.in/admin_dep/images/loader.gif">
                       </center>
                   </div>
                   <center>
                       <button id="ajax_PDF" type="button" class="btn btn-primary">Print Certificate</button>
                   </center>
               </div>
           </div>
       </div>
   </div>
</div>

<script>
   function add_data(certificate_id, student_id, receipt_no) {
       var certificate_content = $('#certificate_html_'+certificate_id).val();
       $('#reprint_certificate_html').html(certificate_content);
       $('#student_id').val(student_id);
       $('#receipt_id_html').val(certificate_id);
       $('#ChapterModal').modal('show');
   }

   function checkAll(ele, name) {
       var checkboxes = document.getElementsByClassName(name);
       for (var i = 0; i < checkboxes.length; i++) {
           if (checkboxes[i].type == 'checkbox') {
               checkboxes[i].checked = ele.checked;
           }
       }
   }
</script>

@include('includes.footerJs')
<script>
   $(document).ready(function() {
       var table = $('#example').DataTable({
           select: true,
           lengthMenu: [[100, 500, 1000, -1], ['100', '500', '1000', 'Show All']],
           dom: 'Bfrtip',
           buttons: [
               { extend: 'pdfHtml5', title: 'Student Certificate Report', orientation: 'landscape', pageSize: 'A0', exportOptions: { columns: ':visible' } },
               { extend: 'csv', text: ' CSV', title: 'Student Certificate Report' },
               { extend: 'excel', text: ' EXCEL', title: 'Student Certificate Report' },
               { extend: 'print', text: ' PRINT', title: 'Student Certificate Report', customize: function(win) {
                   $(win.document.body).prepend(`{!! App\Helpers\get_school_details("", "", "") !!}`);
                   $(win.document.body).append(`<div style="text-align: right;margin-top:20px">Printed on: {{date('d-m-Y H:i:s')}}</div>`);
                    // Add CSS for page numbering in print mode
        var css = `
            @page {
                
                @bottom-right {
                    content: "Page " counter(page) " of " counter(pages);
                }
            }
            body {
                counter-reset: page;
            }
        `;
        var head = win.document.head || win.document.getElementsByTagName('head')[0];
        var style = win.document.createElement('style');
        style.type = 'text/css';
        style.media = 'print';
        if (style.styleSheet){
            style.styleSheet.cssText = css;
        } else {
            style.appendChild(win.document.createTextNode(css));
        }
        head.appendChild(style);

        // Add page number footer element
        $(win.document.body).append('<div class="page-number"></div>');
    }
               
           },
           'pageLength'
       ],
       });

       $('#example thead tr').clone(true).appendTo('#example thead');
       $('#example thead tr:eq(1) th').each(function(i) {
           var title = $(this).text();
           $(this).html('<input type="text" placeholder="Search '+title+'" />');
           $('input', this).on('keyup change', function() {
               if (table.column(i).search() !== this.value) {
                   table.column(i).search(this.value).draw();
               }
           });
       });
   });
</script>
@include('includes.footer')
@endsection
