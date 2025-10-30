@extends('layout')
@section('container')
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Inactive Student Report</h4>
         </div>
      </div>

      <div class="card">
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

         @php
            $grade_id = $standard_id = $division_id = '';
            if(isset($data['grade_id'])){
               $grade_id = $data['grade_id'];
               $standard_id = $data['standard_id'];
               $division_id = $data['division_id'];
            }
         @endphp

         <form action="{{ route('inactive_student_report.create') }}" enctype="multipart/form-data">
            @csrf  
            <div class="row">
               {{ App\Helpers\SearchChain('4','single','grade,std,div',$grade_id,$standard_id,$division_id) }}
               <div class="col-md-4 form-group mt-3">
                  <input type="submit" name="submit" value="Search" class="btn btn-success" >                     
                  <button type="button" class="btn btn-info" data-toggle="modal"
                     data-target="#exampleModal"><i class="mdi mdi-tune"></i></button>
               </div>
            </div>

            <!-- Modal -->
            <div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1"
               role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
               <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Choose Field</h5>
                        <button type="button" class="close" data-dismiss="modal"
                           aria-label="Close">
                        <span aria-hidden="true">x</span>
                        </button>
                     </div>
                     <div class="modal-body">
                        <div class="slimscrollright">
                           <div class="rpanel-title"><span><i class="ti-close right-side-toggle"></i></span> </div>
                           <div class="row">
                              @php $i=0; @endphp
                              @foreach($data['data'] as $header => $headerValue)
                                 @if(isset($data['data'][$header]) && !empty($data['data'][$header]))
                                    @php 
                                       $val = $headerValue->field_name.'/'.$headerValue->id;
                                       $fieldVal = $headerValue->field_name;
                                       $fieldLabel = str_replace('_',' ',$headerValue->field_label);
                                       $label = ucfirst($fieldLabel);
                                       if($fieldVal=="enrollment_no"){
                                           $label = App\Helpers\get_string('grno');
                                       }
                                    @endphp
                                    <div class="col-md-4 form-group py-8">
                                       <div class="pb-2">
                                          <input type="checkbox" name="dynamicFields[]" class="chkClass{{$i}}" value="{{$val}}" 
                                             @if(isset($data['dynamicFields']) && in_array($val,$data['dynamicFields'])) checked @endif> {{$label}}
                                       </div>
                                    </div>
                                 @endif
                                 @php $i++; @endphp
                              @endforeach
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </form>
      </div>

      @if(isset($data['student_data']))
         @php
            $student_data = $data['student_data'];
            $j = 1;
         @endphp
         <div class="card">
            <div class="table-responsive mt-20 tz-report-table">

               {{-- School Details --}}
               
{{-- School Details --}}
<div style="text-align: center; font-family: Arial, Helvetica, sans-serif; font-weight: 700;">
    <div style="font-size: 18px;">
        Shree Swami Atmanand Saraswati Institute of Technology
    </div>
    <div style="font-size: 14px;">
        Managed by Shree Tapi Brahmcharyashram Sabha
    </div>
    <div style="font-size: 14px;">
        Shree Swami Atmanand Saraswati Vidya Sankul
    </div>
    <div style="font-size: 14px;">
        Kapodra, Varachha Road, Surat-395006.  
                                    </br> Academic Year : {{ isset($data['academic_year']) ? $data['academic_year'] : (date('Y') . '-' . (date('Y') + 1)) }}
    </div>
    <div style="font-size: 14px; margin-top: 5px;">
        Academic Section : {{ $data['section'] ?? 'FY-BBA' }} | 
        Semester : {{ $data['semester'] ?? 'BBA SEM - 1' }} | 
        Division : {{ $data['division_label'] ?? 'A' }}
    </div>
</div>


               <table id="example" class="table table-striped">
                  <thead>
                     <tr>
                        @foreach($data['headers'] as $hkey => $header)
                           @php 
                              $joinVal = str_replace(' ','',$header);
                              $lowerCase = strtolower($joinVal);
                              $checkNorm = DB::table('app_language')
                                 ->where('sub_institute_id', session()->get('sub_institute_id'))
                                 ->where('string', $joinVal)
                                 ->first(); 
                              if(!empty($checkNorm)){
                                 $header = $checkNorm->value;
                              }
                           @endphp
                           <th class="text-left"> {{$header}} </th>
                        @endforeach
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($student_data as $key => $value)
                        <tr>
                           @foreach($data['headers'] as $hkey => $header)
                              @if($hkey == 'image')
                                 <td><img height="60" width="60" src="../storage/student/{{$value->$hkey}}"/></td>
                              @elseif(in_array($hkey, ['admission_date','dob','date','birthdate','created_on','birthday','created_at']))
                                 <td> {{ (isset($value->$hkey)) ? date('d-m-Y', strtotime($value->$hkey)) : '-'}} </td>
                              @else
                                 <td> {{$value->$hkey ?? '-'}} </td>
                              @endif
                           @endforeach
                        </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
      @endif
   </div>
</div>

@include('includes.footerJs')
<script>
 function checkAll(chkName){
    $('.'+chkName).each(function() {
            $(this).prop('checked', !$(this).prop('checked'));
        });
 }
</script>

<script>
   $(document).ready(function () {
       var table = $('#example').DataTable({
           ordering: false,
           select: true,
           lengthMenu: [
               [100, 500, 1000, -1],
               ['100', '500', '1000', 'Show All']
           ],
           dom: 'Bfrtip',
           buttons: [
               {
                   extend: 'pdfHtml5',
                   title: 'Student Report',
                   orientation: 'landscape',
                   pageSize: 'A0',
                   exportOptions: {
                       columns: ':visible'
                   },
               },
               {extend: 'csv', text: ' CSV', title: 'Student Report'},
               {extend: 'excel', text: ' EXCEL', title: 'Student Report'},
               {
                   extend: 'print',
                   text: ' PRINT',
                   title: 'Student Report',
                   customize: function (win) {
                       $(win.document.body).prepend(`{!! App\Helpers\get_school_details("$grade_id", "$standard_id", "$division_id") !!}`);
                       $(win.document.body).append(`<div style="text-align: right;margin-top:20px">Printed on: {{date('d-m-Y H:i:s')}}</div>`);
                   }
               },
               'pageLength'
           ],
       });

       $('#example thead tr').clone(true).appendTo('#example thead');
       $('#example thead tr:eq(1) th').each(function (i) {
           var title = $(this).text();
           $(this).html('<input type="text" placeholder="Search ' + title + '" />');

           $('input', this).on('keyup change', function () {
               if (table.column(i).search() !== this.value) {
                   table.column(i).search(this.value).draw();
               }
           });
       });
   });
</script>

@include('includes.footer')
@endsection
