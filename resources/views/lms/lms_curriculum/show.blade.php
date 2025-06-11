@extends('layout')
@section('container')
<style>
.contentContainer {
width: 100%;
max-width: 1200px;
margin: 20px auto;
}

/* contentHeader Section */
.contentHeader {
text-align: center;
padding: 20px;
background-color: #1d96ba;
color: white;
border-radius: 8px;
margin-bottom: 20px;
}

.contentHeader .logo {
width: 80px;
margin-bottom: 10px;
}

.contentHeader h1 {
margin: 0;
font-size: 24px;
font-weight: 700;
}

.contentHeader h2 {
margin: 5px 0 0;
/* font-size: 18px; */
font-weight: 500;
}

.contentHeader h3 {
margin: 5px 0 0;
font-size: 18px !important;
font-weight: 500;
}
/* Card Styles */
.card {
background-color: white;
border-radius: 8px;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
padding: 20px;
margin-bottom: 20px;
}

.card h2 {
margin-top: 0;
color: #4CAF50;
font-weight: 700;
}

.card p, .card ul {
margin: 10px 0;
font-size: 16px !important;
}

.card ul {
padding-left: 20px;
}

.card ul li {
margin-bottom: 8px;
list-style: inside;
}

/* Table Styles */
table {
width: 100%;
border-collapse: collapse;
margin-top: 10px;
}

table th, table td {
border: 1px solid #ddd;
padding: 10px;
text-align: left;
font-size:16px;
}

table th {
background-color: #1d96ba;
color: white !important;
font-weight: 700;
text-align: left !important;
}

/* Row and Column Layout */
.row {
display: flex;
gap: 20px;
}

.column {
flex: 1;
}

/* Responsive Design */
@media (max-width: 768px) {
.row {
    flex-direction: column;
}
}
</style>
@php 
    $model_integration=[];
    if(isset($data['searchData']->model_integration)){
        $model_integration = explode(',',$data['searchData']->model_integration);
    }
    echo "<pre>";print_r($data['searchData']->model_integration);echo "</pre>";
@endphp
<div id="page-wrapper">
    <div class="container-fluid">

        <div class="contentContainer">
            <!-- contentHeader Section -->
            <div class="row">
            <div class="contentHeader col-md-12">
                <img src="../../../storage{{$data['searchData']->display_image}}" alt="logo" class="logo">
                <h1>{{$data['boards'][$data['searchData']->board_id]}} : {{$data['searchData']->standard_name}} {{ strtoupper($data['searchData']->subject_name) }} 
                    <br/>
                    @foreach($model_integration as $k => $v)
                        {{isset($data['model_integrations'][$v]) ? ($k+1).') '.$data['model_integrations'][$v] : ''}}     
                    @endforeach 
                    CURRICULAM
                </h1>
            </div>
            </div>
    
            <!-- Objective Section -->
            <div class="row">
            <div class="card col-md-12">
                <h2>Objective</h2>
                <hr>
                <p>{!! $data['searchData']->objective !!}</p>
            </div>
        </div>
            <!-- Curriculum Alignment Section -->
            <div class="row">
            <div class="card  col-md-12">
                <h2>Curriculum Alignment</h2>
                <hr>
                <p>{!! $data['searchData']->curriculum_alignment !!}</p>
            </div>
        </div>
            <!-- Holistic Curriculum Section -->
            <div class="row">
            <div class="card  col-md-12">
                <h2>Holistic Curriculum</h2>
                <hr>
                <p>{!! $data['searchData']->holistic_curriculum !!}</p>
            </div>
        </div>
            <!-- Chapter and Assessment Tool Section -->
            <div class="row">
                    <div class="column">
                        <div class="card">
                            <h2>UNIT/TOPIC</h2>
                            <hr>
                            <p>{!! $data['searchData']->chapter !!}</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="card">
                        <h2>ASSESSMENT TOOL</h2>
                        <hr>
                        <p>{!! $data['searchData']->assessment_tool !!}</p>
                        </div>
                    </div>
                </div>

                {{-- co po starts  --}}
        <div class="row">
            <div class="card col-md-12">
                <h2>CO PO Mapping</h2>
                <hr>
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            @foreach($data['poData'] as $key=>$po)
                            <th title="{{$po->title}}">{{$po->short_code}}</th>
                            @endforeach
                        </tr>
                    </thead>
                    @php
                        $poAvg = []; // array to store sum and count per PO
                    @endphp

                    <tbody>
                        @foreach($data['coData'] as $key => $co)
                            @php 
                                $value = isset($data['coPoMapping'][$co->id]) ? json_decode($data['coPoMapping'][$co->id], true) : [];
                            @endphp
                            <tr>
                                <th title="{{ $co->title }}">CO{{ $key + 1 }}</th>
                                @foreach($data['poData'] as $pk => $po)
                                    @php 
                                        $val = $value[$po->id] ?? null;

                                        // Track sum and count for averages
                                        if ($val !== null && is_numeric($val)) {
                                            $poAvg[$po->id]['sum'] = ($poAvg[$po->id]['sum'] ?? 0) + $val;
                                            $poAvg[$po->id]['count'] = ($poAvg[$po->id]['count'] ?? 0) + 1;
                                        }
                                    @endphp 
                                    <td class="text-left">{{ $val ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach

                        <tr>
                            <th>Average</th>
                            @foreach($data['poData'] as $po)
                                @php
                                    $sum = $poAvg[$po->id]['sum'] ?? 0;
                                    $count = $poAvg[$po->id]['count'] ?? 0;
                                    $average = ($count > 0) ? number_format($sum / $count, 2) : '';
                                @endphp
                                <td class="text-left">{{ $average }}</td>
                            @endforeach
                        </tr>
                    </tbody>

                </table>
            </div>
        </div>
        {{-- co po ends  --}}

            </div>
        </div>

        

    </div> <!-- Closing contentContainer-fluid -->
</div>
@include('includes.footerJs')
@include('includes.footer')
@endsection