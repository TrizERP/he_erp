@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<style>
   
    </style>
<div id="page-wrapper">
    <div class="container-fluid">        
            <div class="card">
                @if(!empty($data['message']))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $data['message'] }}</strong>
                </div>
                @endif
                <div class="col-lg-3 col-sm-3 col-xs-3">
                    <a href="{{ route('subject_wise_grade_master.create') }}" class="btn btn-info add-new"><i class="fa fa-plus"></i> Add New</a>
                </div>
                
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <table id="example" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th>Exam Type</th>
                                <th>App Status</th>
                                <th>{{App\Helpers\get_string('standard','request')}}</th>
                                <th>Subject</th>
                                <th>CO</th>
                                <th>Exam Name</th>
                                <th>Points</th>
                                <th>Sort Order</th>
                                <th>Exam Date</th>
                                <th class="text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            

                        </tbody>

                    </table>

                </div>

            </div>      
    </div>
</div>

@include('includes.footerJs')
<script>
    $(document).ready(function() {
     var table = $('#example').DataTable( {
            
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
