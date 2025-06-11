@extends('layout')
@section('container')
<div id="page-wrapper">
    <div class="container-fluid">
		<div class="row bg-title">
			<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
				<h4 class="page-title">Co-Po Mapping</h4> 
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
            <form action="{{route('co_po_mapping.create')}}" method="get">
                <div class="row">
                    {{ App\Helpers\SearchChain('3','','grade,std',$grade,$standard) }}
                    <div class="col-md-3">
                        <label for="subject">Select Subject:</label>
                        <select name="subject" id="subject" class="form-control" required>
                            <option value="">Select Subject</option>
                        
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

        @if(isset($data['co_data']) && isset($data['po_data']) && !empty($data['co_data']) && !empty($data['po_data']))
        <div class="card">
            <form action="{{route('co_po_mapping.store')}}" method="post">
                @csrf 
                <input type="hidden" name="grade_id" value="{{$grade}}">
                <input type="hidden" name="standard_id" value="{{$standard}}">
                <input type="hidden" name="subject_id" value="{{$subject}}">
                <div class="table table-responsive">
                    <table class="table table-striped" id="example">
                        <thead>
                            <tr>
                                <th></th>
                                @foreach($data['po_data'] as $po)
                                    <th class="text-left" title="{{$po->title}}">{{$po->short_code}}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['co_data'] as $k=> $co)
                            <tr>
                                @php 
                                    $value = (isset($data['addedData'][$co->id])) ? json_decode($data['addedData'][$co->id], true) : [];
                                @endphp
                                <td title="{{$co->title}}">CO{{$k+1}}</td>
                                @foreach($data['po_data'] as $pk=>$po)
                                    <td class="text-left">
                                        <input type="number" max="3" name="poInput[{{ $co->id }}][{{$po->id}}]" class="form-control po-input" data-po-id="{{$po->id}}" @if(!empty($value) && isset($value[$po->id])) value="{{$value[$po->id]}}" @endif>
                                    </td>
                                 @endforeach
                            </tr>
                            @endforeach
                            <tr>
                                <td>Average</td>
                                @foreach($data['po_data'] as $pk=>$po)
                                    <td class="text-left">
                                        <input type="number" id="avg_{{$po->id}}" class="form-control avg-input" readonly>
                                    </td>
                                @endforeach
                            </tr>
                        
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12">
                    <center>
                        <input type="submit" value="Save" name="save" class="btn btn-success" style="margin-top: 20px;">
                    </center>
                </div>
            </form>
        </div>
        @endif

    </div>
</div>
@include('includes.footerJs')
<script>
$(document).ready(function() {
    @if($subject != '')
        getSubject({{$standard}}, {{$subject}});   
    @endif
    $("#standard").change(function() {
        getSubject($(this).val());
    });

    // Calculate averages for all columns on page load
    $('.po-input').each(function() {
        let poId = $(this).data('po-id');
        let total = 0;
        let count = 0;

        $(`.po-input[data-po-id="${poId}"]`).each(function() {
            let value = parseFloat($(this).val());
            if (!isNaN(value)) {
                total += value;
                count++;
            }
        });

        let average = count > 0 ? (total / count).toFixed(2) : '';
        $(`#avg_${poId}`).val(average);
    });
});

// Recalculate averages on keyup
$(document).on('keyup', '.po-input', function() {
    let poId = $(this).data('po-id');
    let total = 0;
    let count = 0;

    $(`.po-input[data-po-id="${poId}"]`).each(function() {
        let value = parseFloat($(this).val());
        if (!isNaN(value)) {
            total += value;
            count++;
        }
    });

    let average = count > 0 ? (total / count).toFixed(2) : '';
    $(`#avg_${poId}`).val(average);
});
function getSubject(std_id,salVal=''){
        var path = "{{ route('ajax_StandardwiseSubject') }}";
        $('#subject').find('option').remove().end().append('<option value="">Select Subject</option>').val('');
        $.ajax({
            url: path,
            data:'std_id='+std_id,
            success: function(result){
                for(var i=0;i < result.length;i++){
                    var selected = '';
                    if(salVal!='' && salVal == result[i]['subject_id']){
                        selected = 'selected';
                    }
                    $("#subject").append($("<option "+selected+"></option>").val(result[i]['subject_id']).html(result[i]['display_name']));
                }
            }
        });
}
</script>
@include('includes.footer')
@endsection