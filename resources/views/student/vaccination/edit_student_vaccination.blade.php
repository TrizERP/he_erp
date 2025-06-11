@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Edit Student Vaccination</h4>
            </div>
        </div>
        <div class="card">
			<!-- @TODO: Create a saperate tmplate for messages and include in all tempate -->
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
            <div class="row">                
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <form action="{{ route('student_vaccination.update',$data['id']) }}" enctype="multipart/form-data" method="post">
                    {{ method_field("PUT") }}
                    @csrf
                        <div class="row">                            
                            <div class="col-md-4 form-group">
                                <label>Student </label>
                                <input type="text" id='student' value="@if(isset($data['student_name'])){{ $data['student_name'] . ' - '. $data['student_id'] }}@endif" list="studentSearchList" name="student_id" onkeyup="getStudents(this.value);" required="required" class="form-control">
                                <datalist id="studentSearchList">
                                </datalist>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Doctor Name </label>
                                <input type="text" id='doctor_name' value="@if(isset($data['doctor_name'])){{ $data['doctor_name'] }}@endif" name="doctor_name" class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Doctor Contact </label>
                                <input type="text" id='doctor_contact' value="@if(isset($data['doctor_contact'])){{ $data['doctor_contact'] }}@endif" name="doctor_contact" class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Date</label>
                                <input type="text" id='date' value="@if(isset($data['date'])){{ $data['date'] }}@endif" name="date" class="form-control mydatepicker">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Vaccination Type </label>
                                <input type="text" id='vaccination_type' value="@if(isset($data['vaccination_type'])){{ $data['vaccination_type'] }}@endif" name="vaccination_type" class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Note </label>
                                <textarea type="text" id='note' rows="6" name="note" class="form-control">
                                    @if(isset($data['note'])){{ $data['note'] }}@endif
                                </textarea>
                            </div>                            
                            <div class="col-md-12 form-group">
                                <center>                                    
                                    <input type="submit" name="submit" value="Update" class="btn btn-success" >
                                </center>
                            </div>
                        </div>
                    </form>
                </div>
            </div>    
        </div>
    </div>    
</div>

@include('includes.footerJs')

<script type="text/javascript">
    function getStudents(value)
    {
        var URL = "{{route('search_student_name')}}";
        // var data = value;

        if(value.length > 2)
        {

            $.post(URL,
          {
            'value': value
          },
          function(result, status){
            $('#studentSearchList').find('option').remove().end();
            for(var i=0;i < result.length;i++){

                    $("#studentSearchList").append($("<option></option>").val(result[i]['student'] + ' - ' + result[i]['id']).html(result[i]['student']));
            }
          });
        }
    }
</script>

@include('includes.footer')
