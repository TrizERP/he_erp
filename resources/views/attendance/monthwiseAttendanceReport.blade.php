@extends('layout')
@section('container')
<div id="page-wrapper">
    <div class="container-fluid">

    	<div class="row bg-title">
          	<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                   <h4 class="page-title">All Subject Semesterwise Report</h4> </div>
            </div>
         </div>
          @php
            $grade_id = $standard_id = $division_id = $enrollment_no = $receipt_no =$to_date = '';
 			$from_date = now();
            if(isset($data['grade_id'])){
                $grade_id = $data['grade_id'];
                $standard_id = $data['standard_id'];
                $division_id = $data['division_id'];
            }
            @endphp
        <div class="card">
            <div class="row">
                {{ App\Helpers\SearchChain('2','single','grade,std,div',$grade_id,$standard_id,$division_id) }}
            </div>
        </div>
	</div>
</div>

@include('includes.footerJs')
 @include('includes.footer')
@endsection