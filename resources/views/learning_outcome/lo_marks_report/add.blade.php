@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')


<div id="page-wrapper">
    <div class="container-fluid">
        <div class="card">
            @if(!empty($data['message']))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $data['message'] }}</strong>
            </div>
            @endif
            <div class="row">
                <div class="col-lg-3 col-sm-3 col-xs-3">
                    {{-- <a href="{{ route('lo_master.create') }}" class="btn btn-info add-new"><i
                        class="fa fa-plus"></i> Add New</a> --}}
                </div>
            </div>
                <form action="{{ route('lo_marks_entry.store') }}" enctype="multipart/form-data" method="post">
                    @csrf
                <input type="hidden" name="examdate" value="{{ $data['examdate'] }}">
                <input type="hidden" name="medium" value="{{ $data['medium'] }}">
                <input type="hidden" name="std" value="{{ $data['std'] }}">
                <input type="hidden" name="div" value="{{ $data['div'] }}">
                <input type="hidden" name="subject" value="{{ $data['subject'] }}">
                <input type="hidden" name="questionIds" id="questionIds" value="{{ $data['questions_ids'] }}">
                    <div class="row">
                        <div class="col-lg-12 col-sm-12 col-xs-12">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Sr No.</th>
                                        <th>Roll No</th>
                                        <th>Name</th>
                                        <th>{{App\Helpers\get_string('standard','request')}}</th>
                                        @foreach ($data['questions'] as $question=>$mark)
                                        @php
                                        $ids = explode('--',$question);
                                        $que = $ids[0];
                                        $id = $ids[1];
                                        @endphp
                                        <th>{{ $que }}({{ $mark }})</th>
                                        @endforeach
                                        <th>Total Mark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $i=1;
                                    @endphp
                                    @foreach($data['stud'] as $key => $datas)
                                    <tr id={{$datas->ID}}>
                                        <td>{{$i}}</td>
                                        <td>{{$datas->roll_no}}</td>
                                        <td>{{$datas->name}}</td>
                                        <td>{{$datas->STD}}</td>
                                        @foreach ($data['questions'] as $question=>$mark)
                                        @php
                                        $ids = explode('--',$question);
                                        $que = $ids[0];
                                        $id = $ids[1];
                                        $resultQid = "QID".$id;
                                        @endphp
                                        <td>
                                            {{ $datas->$resultQid }}
                                        </td>
                                        @endforeach
                                        <td>
                                            {{$datas->total}}
                                        </td>
                                    </tr>
                                    @php
                                    $i++;
                                    @endphp
                                    @endforeach
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
        </div>
    </div>
</div>

@include('includes.footerJs')
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js"></script>
<script>
    var checkMarksError = 0;
function validateMarks(marks,x,studentId){
    // alert(marks);
    // alert(x.value);
  calculateMarks(x.id,studentId);
  if(parseInt(x.value) > parseInt(marks)){
    alert("You can't enter marks more than exam marks.");
    document.getElementById(x.id).focus();
    // x.style.border="thick solid red";
    x.setAttribute("style", "border : 2px solid red !important; outline : none;width: 44px;");
    checkMarksError = checkMarksError + 1;
  }else if(x.value < 0){
    alert("You can't enter marks less then 0.");
    document.getElementById(x.id).focus();
    // x.style.border="thick solid red";
    x.setAttribute("style", "border : 2px solid red !important; outline : none;width: 44px;");
    checkMarksError = checkMarksError + 1;
  }else{
    x.setAttribute("style", "border : 1px solid #aaaaaa !important;width: 44px;");
    checkMarksError = checkMarksError - 1;
  }
  return false;
}
function calculateMarks(id,studentId){
  var str = document.getElementById("questionIds").value;
//   console.log(str);
  var res = str.split(",");
  var total = 0;
//   console.log(studentId);
  for(i=0;i<res.length;i++){
    var getId = "result["+studentId+"]["+res[i]+"]";
    var marks = document.getElementById(getId).value;
    if(marks != ''){
    // console.log(marks);
    total += parseFloat(marks);
    }
    document.getElementById("resultTotal["+studentId+"]").value = total;
  }
}
function validateMarksError(){
  if(checkMarksError > 0){
    alert("Please fix all errors");
    return false;
  }else{
    return true;
  }
}
    $(document).ready(function () {
                                                    var table = $('#example').DataTable({
                                                    });
                                                });

</script>
@include('includes.footer')
