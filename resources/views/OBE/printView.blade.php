@extends('layout')
@section('container')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-3 mt-2 col-sm-4 col-xs-12">
                <h4 class="page-title">Print CO PO</h4> 
            </div>
        </div>        
        @php 
        $grade = $standard = $subject = '';
        if(isset($data['grade_id'])){
            $grade = $data['grade_id'];
        }
        if(isset($data['standard_id'])){
            $standard = $data['standard_id'];
        }
        if(isset($data['subject_id'])){
            $subject = $data['subject_id'];
        }
        @endphp
        <div class="card">
            @if ($sessionData = Session::get('data'))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $sessionData['message'] }}</strong>
                </div>
            @endif

            @if (isset($data['status']) && $data['status'] == 0)
                <div class="alert alert-danger alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $data['message'] }}</strong>
                </div>
            @endif
            <form action="{{route('print_co_po.create')}}" method="get">
                <div class="row">
                    {{ App\Helpers\SearchChain('3','','grade,std',$grade,$standard) }}
                    <div class="col-md-3">
                        <label for="subject">Select Subject:</label>
                        <select name="subject" id="subject" class="form-control" required>
                            <option value="">Select Subject</option>
                        
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="">Print Type</label>
                        <select name="print_type" id="print_type" class="form-control">
                            @foreach($data['printType'] as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <center>
                            <input type="submit" value="Search" name="search" class="btn btn-primary" style="margin-top: 20px;">
                        </center>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
@include('includes.footerJs')
<script>
$(document).ready(function() {
    $('#standard').on('change', function() {
        var std_id = $(this).val();
        var path = "{{ route('ajax_StandardwiseSubject') }}";
        $('#subject').find('option').remove().end().append('<option value="">Select Subject</option>').val('');
        $.ajax({
            url: path,
            data:'std_id='+std_id, 
            success: function(result){
                for(var i=0;i < result.length;i++){
                    $("#subject").append($("<option></option>").val(result[i]['subject_id']).html(result[i]['display_name']));
                }
            }
        });
    });
});
</script>
@include('includes.footer')
@endsection
