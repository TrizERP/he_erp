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
                <h1 style="text-align: center; font-size: 20px; margin-top: 5px;">Mid-Sem Exam Report</h1>
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped">                       
                             <thead>
                                <tr>
                                    <th>Enrollment No.</th>
                                    @if(isset($data['date_arr']))
                                        @foreach($data['date_arr'] as $k => $date_point)
                                            <th style="text-align: center;">{{ $date_point }}</th>
                                        @endforeach
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Initialize subject summary counters
                                    $subjectSummary = [];
                                @endphp

                                {{-- Student-wise rows --}}
                                @foreach($all_student as $key => $all_data)
                                    @php
                                        $student_id = $all_data['id'];
                                    @endphp
                                    <tr>
                                        <td>{{ $all_data['enrollment_no'] }}</td>

                                        @if(isset($data['date_arr']))
                                            @foreach($data['date_arr'] as $k => $date_point)
                                                @php
                                                    if (!isset($subjectSummary[$k])) {
                                                        $subjectSummary[$k] = [
                                                            'total' => 0,
                                                            'pass' => 0,
                                                            'fail' => 0,
                                                            'absent' => 0,
                                                        ];
                                                    }
                                                @endphp

                                                @if(isset($data['WRT_data'][$student_id][$k]) && count($data['WRT_data'][$student_id][$k]) > 0)
                                                    @php $subjectSummary[$k]['total']++; @endphp

                                                    @if($data['WRT_data'][$student_id][$k]['is_absent'] == 'AB')
                                                        <td style="text-align: center;font-weight:bold; color:red;">AB</td>
                                                        @php $subjectSummary[$k]['absent']++; @endphp
                                                    @else
                                                        @php
                                                            $marks = $data['WRT_data'][$student_id][$k]['obtained_points'];
                                                            if ($marks >= 12) {
                                                                $subjectSummary[$k]['pass']++;
                                                            } else {
                                                                $subjectSummary[$k]['fail']++;
                                                            }
                                                        @endphp
                                                        <td style="text-align: center;">{{ $marks }}</td>
                                                    @endif
                                                @else
                                                    <td style="text-align: center;">-</td>
                                                @endif
                                            @endforeach
                                        @endif
                                    </tr>
                                @endforeach

                                {{-- Summary Rows --}}
                                <tr style="font-weight:bold; background:#f9f9f9;">
                                    <td style="text-align: center;">Total</td>
                                    @foreach($data['date_arr'] as $k => $date_point)
                                        <td style="text-align: center;">{{ $subjectSummary[$k]['total'] ?? 0 }}</td>
                                    @endforeach
                                </tr>
                                <tr style="font-weight:bold;">
                                    <td style="text-align: center;">Pass</td>
                                    @foreach($data['date_arr'] as $k => $date_point)
                                        <td style="text-align: center;">{{ $subjectSummary[$k]['pass'] ?? 0 }}</td>
                                    @endforeach
                                </tr>
                                <tr style="font-weight:bold;">
                                    <td style="text-align: center;">Fail</td>
                                    @foreach($data['date_arr'] as $k => $date_point)
                                        <td style="text-align: center;">{{ $subjectSummary[$k]['fail'] ?? 0 }}</td>
                                    @endforeach
                                </tr>
                                <tr style="font-weight:bold;">
                                    <td style="text-align: center;">Absent</td>
                                    @foreach($data['date_arr'] as $k => $date_point)
                                        <td style="text-align: center;">{{ $subjectSummary[$k]['absent'] ?? 0 }}</td>
                                    @endforeach
                                </tr>
                                <tr style="font-weight:bold;">
                                    <td style="text-align: center;">Per.(%)</td>
                                    @foreach($data['date_arr'] as $k => $date_point)
                                        @php
                                            $total = $subjectSummary[$k]['total'] ?? 0;
                                            $pass = $subjectSummary[$k]['pass'] ?? 0;
                                            $percent = ($total > 0) ? round(($pass / $total) * 100, 2) : 0;
                                        @endphp
                                        <td style="text-align: center;">{{ $percent }}%</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>

                        <!-- Signature Section -->
                        <div style="margin-top: 60px; padding: 20px 0; width: 100%;color:black">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                                <div style="text-align: left; width: 20%;">
                                    <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                        Sign of Class Coordinator
                                    </div>
                                </div>
                                <div style="text-align: center; width: 20%;">
                                    <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                        Sign of HOD.
                                    </div>
                                </div>
                                <div style="text-align: center; width: 20%;">
                                    <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                        Sign of Exam Coordinator
                                    </div>
                                </div>
                                <div style="text-align: center; width: 20%;">
                                    <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                        Sign of GTU Coordinator
                                    </div>
                                </div>
                                <div style="text-align: right; width: 20%;">
                                    <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                        Sign of Principal
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
        //select: false,          
        paging: false,          // Enable pagination
        //pageLength: 500,        // Rows per page
        //lengthMenu: [5, 10, 25, 50, 100], // Page size options
        ordering: false,        // Enable sorting
        searching: false,       // Enable search box
        info: false,            // Show "Showing 1 to n of n entries"
        //autoWidth: false,
        dom: 'Bfrtip', 
        buttons: [ 
            /*{ 
                extend: 'pdfHtml5',
                title: 'Mid-Sem Exam Report',
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
                                    text: '_________________\nSignature of Internal Examiner',
                                    alignment: 'left',
                                    width: '45%'
                                },
                                {
                                    text: '_________________\nSignature of External Examiner/HOD',
                                    alignment: 'right',
                                    width: '45%'
                                }
                            ],
                            margin: [20, 0, 20, 0]
                        });
                    }
            }, */
            { extend: 'csv', text: ' CSV', title: 'Mid-Sem Exam Report' }, 
            /*{ extend: 'excel', text: ' EXCEL', title: 'Mid-Sem Exam Report' }, */
            { extend: 'print', text: ' PRINT', title: 'Mid-Sem Exam Report',customize: function (win) {
                $(win.document.body).find('h1').css('text-align', 'center').css('font-size', '20px').css('margin-top', '5px');
                $(win.document.body).find('th, td').css('color', 'black').css('text-align', 'center').css('vertical-align', 'middle');
                $(win.document.body).prepend(`{!! App\Helpers\get_school_details($grade_id ?? '', $standard_id ?? '', $division_id ?? '') !!}`);
                        
                // Custom formatted date: DD-MM-YYYY hh:mmAM/PM
                const now = new Date();
                const day = String(now.getDate()).padStart(2, '0');
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const year = now.getFullYear();
                let hours = now.getHours();
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;
                const formattedDateTime = `${day}-${month}-${year} ${hours}:${minutes}${ampm}`;
                // Add signature section to print view
                $(win.document.body).append(`
                    <div style="margin-top: 60px; padding: 20px 0; width: 100%;color:black">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                            <div style="text-align: left; width: 20%;">
                                <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                    Sign of Class Coordinator
                                </div>
                            </div>
                            <div style="text-align: center; width: 20%;">
                                <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                    Sign of HOD.
                                </div>
                            </div>
                            <div style="text-align: center; width: 20%;">
                                <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                    Sign of Exam Coordinator
                                </div>
                            </div>
                            <div style="text-align: center; width: 20%;">
                                <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                    Sign of GTU Coordinator
                                </div>
                            </div>
                            <div style="text-align: right; width: 20%;">
                                <div style="border-top: 1px solid #000; padding-top: 5px; display: inline-block;">
                                    Sign of Principal
                                </div>
                            </div>
                        </div>
                        <div style="text-align: left; margin-top: 20px;">
                            Printed on: ${formattedDateTime}
                        </div>
                    </div>
                `);
                    }},
        ], 
        }); 
        //$('#example thead tr').clone(true).appendTo( '#example thead' );
        /*$('#example thead tr:eq(1) th').each( function (i) {
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
        } );*/
    } );
</script>

@include('includes.footer')