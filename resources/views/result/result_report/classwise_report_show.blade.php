@include('includes.headcss')
<style>
    tfoot input {
        width: 100%;
        padding: 3px;
        box-sizing: border-box;
    }
    tfoot {
     display: table-header-group;
    }
    .signature-section {
        margin-top: 50px;
        padding: 20px 0;
        width: 100%;
    }
    .signature-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        width: 100%;
    }
    .signature-left {
        text-align: left;
        width: 48%;
    }
    .signature-right {
        text-align: right;
        width: 48%;
    }
    .signature-line {
        border-top: 1px solid #000;
        padding-top: 5px;
        display: inline-block;
    }
    .signature-left .signature-line {
        text-align: left;
        margin-left: 0;
    }
    .signature-right .signature-line {
        text-align: right;
        margin-right: 0;
    }
</style>
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Classwise Report</h4> 
            </div>
        </div>        
        @if(isset($data['all_student']))
            @php
                if(isset($data['all_student']))
                {
                    $all_student = $data['all_student'];
                }
                
                // Get grade_id, standard_id, division_id from the data if available
                $grade_id = $data['grade_id'] ?? '';
                $standard_id = $data['standard_id'] ?? '';
                $division_id = $data['division_id'] ?? '';
            @endphp 
            <div class="card">  
                @php
                    // Display school details like in monthwise_attendance_report
                    echo App\Helpers\get_school_details($grade_id, $standard_id, $division_id);
                    
                    // Display additional report information if available
                    if(isset($data['subject_name']) || isset($data['date_range'])) {
                        echo '<br><center>';
                        if(isset($data['subject_name'])) {
                            echo '<span style="font-size: 14px;font-weight: 600;font-family: Arial, Helvetica, sans-serif !important">Subject : ' . $data['subject_name'] . '</span>';
                        }
                        if(isset($data['date_range'])) {
                            echo '<span style="font-size: 14px;font-weight: 600;font-family: Arial, Helvetica, sans-serif !important"> | Date Range : ' . $data['date_range'] . '</span>';
                        }
                        echo '</center><br>';
                    }
                @endphp
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped">                       
                             <thead>
                                <tr>
                                    <!--<th>Standard</th>-->
                                    <!--<th>Roll No.</th>-->
                                    <th>Student Name</th>
                                        @if(isset($data['date_arr']))
                                            @foreach($data['date_arr'] as $k => $date_point)
                                                <th>{{$date_point}}</th>
                                            @endforeach
                                        @endif
                                    <th>Total</th>
                                    <th>Percentage(%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($all_student as $key => $all_data)
                                    @php
                                        $total = $obtained_total = $percentage = 0;
                                        $student_id = $all_data['id'];
                                    @endphp                                    
                                    <tr>
                                        <!--<td>{{$all_data['standard_name']}} - {{$all_data['division_name']}}</td>-->
                                        <!--<td>{{$all_data['roll_no']}}</td>    -->
                                        <td>{{$all_data['first_name']}} {{$all_data['middle_name']}} {{$all_data['last_name']}}</td>
                                        @if(isset($data['date_arr']))
                                        @foreach($data['date_arr'] as $k => $date_point)
                                            @if(isset($data['WRT_data'][$student_id][$k]) && count($data['WRT_data'][$student_id][$k]) > 0)
                                                    @if($data['WRT_data'][$student_id][$k]['is_absent'] == 'AB')
                                                        <td style="font-weight: bold;color:red;">{{$data['WRT_data'][$student_id][$k]['is_absent']}}</td>
                                                    @else
                                                        <td>{{$data['WRT_data'][$student_id][$k]['obtained_points']}}</td>
                                                    @endif
                                                @php
                                                    $total = $total + $data['WRT_data'][$student_id][$k]['total_points'];
                                                    $obtained_total = $obtained_total + $data['WRT_data'][$student_id][$k]['obtained_points'];
                                                    $per = (($obtained_total * 100) / $total);
                                                    $percentage = number_format($per,2);
                                                @endphp
                                                @else
                                              <td>
                                                    -
                                              </td>
                                            @endif
                                        @endforeach
                                        @endif
                                        <td>{{$obtained_total}}/{{$total}}</td>
                                        <td>{{$percentage}}</td>
                                    </tr>
                                @endforeach
                            </tbody>    
                        </table>

                        <!-- Signature Section -->
                        <div class="signature-section">
                            <div class="signature-container">
                                <div class="signature-left">
                                    <div class="signature-line">
                                        Signature of Internal Examiner
                                    </div>
                                </div>
                                <div class="signature-right">
                                    <div class="signature-line">
                                        Signature of External Examiner/HOD
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>     
                </div>
            </div>    
        @endif           
    </div>
</div>

@include('includes.footerJs')

<script>
    $(document).ready(function() {
    var table = $('#example').DataTable( {
         select: true,          
         lengthMenu: [ 
                        [100, 500, 1000, -1], 
                        ['100', '500', '1000', 'Show All'] 
        ],
        dom: 'Bfrtip', 
        buttons: [ 
            { 
                extend: 'pdfHtml5',
                title: 'Classwise Report',
                orientation: 'landscape',
                pageSize: 'LEGAL',                
                pageSize: 'A0',
                exportOptions: {                   
                     columns: ':visible'                             
                },
                customize: function (doc) {
                        var headerContent = `{!! htmlspecialchars_decode(App\Helpers\get_school_details($grade_id ?? '', $standard_id ?? '', $division_id ?? '')) !!}`;

                        var tmp = document.createElement("div");
                        tmp.innerHTML = headerContent;
                        var decodeHeader = tmp.textContent || tmp.innerText;

                        // Add header
                        doc.content.unshift({
                            text: decodeHeader,
                            alignment: 'center',
                        });
                        
                        // Add signature section to PDF
                        doc.content.push({
                            text: '\n\n\n\n\n\n',
                        });
                        
                        doc.content.push({
                            columns: [
                                {
                                    text: '_____________________\nSignature of Internal Examiner',
                                    alignment: 'left',
                                    width: '45%'
                                },
                                {
                                    text: '_____________________\nSignature of External Examiner/HOD',
                                    alignment: 'right',
                                    width: '45%'
                                }
                            ],
                            margin: [20, 0, 20, 0]
                        });
                    }
            }, 
            { extend: 'csv', text: ' CSV', title: 'Classwise Report' }, 
            { extend: 'excel', text: ' EXCEL', title: 'Classwise Report' }, 
            { extend: 'print', text: ' PRINT', title: 'Classwise Report',customize: function (win) {
                        $(win.document.body).prepend(`{!! App\Helpers\get_school_details($grade_id ?? '', $standard_id ?? '', $division_id ?? '') !!}`);
                        
                        // Add signature section to print view
                        $(win.document.body).append(`
                            <div style="margin-top: 80px; padding: 20px 0; width: 100%;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                                    <div style="text-align: left; width: 48%;">
                                        <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                            Signature of Internal Examiner
                                        </div>
                                    </div>
                                    <div style="text-align: right; width: 48%;">
                                        <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                            Signature of External Examiner/HOD
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    }}, 
            'pageLength' 
        ], 
        }); 
        $('#example thead tr').clone(true).appendTo( '#example thead' );
        $('#example thead tr:eq(1) th').each( function (i) {
            var title = $(this).text();
            $(this).html( '<input type="text" placeholder="Search '+title+'" />' );

            $( 'input', this ).on( 'keyup change', function () {
                if ( table.column(i).search() !== this.value ) {
                    table
                        .column(i)
                        .search( this.value )
                        .draw();
                }
            } );
        } );
    } );
</script>

@include('includes.footer')