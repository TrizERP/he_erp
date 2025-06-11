@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<style type="text/css">
    #overlay {
      position: fixed; /* Sit on top of the page content */
      display: none; /* Hidden by default */
      width: 100%; /* Full width (cover the whole page) */
      height: 100%; /* Full height (cover the whole page) */
      top: 0; 
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0,0,0,0.5); /* Black background with opacity */
      z-index: 2; /* Specify a stack order in case you're using a different order for other elements */
      cursor: pointer; /* Add a pointer on hover */
    }
    .setWatermark{
        padding : 0px !important;
    }
</style>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Other Fees Collect Receipt</h4>
            </div>
        </div>
        <div id="printPage" class="card">            
            
          

            	@if(isset($data["str"]))
                @php
                        $page = "";
                        if($data['page_size'] == "A5"){
                            $page = '<page size="A5" layout="landscape">{!!$data["str"]!!}';
                        }
                        else if($data['page_size'] == "A5DB")
                        {
                            $page = '<page size="A5" layout="landscape">';
                    @endphp
                            <table width="100%">
                                <tr>
                                    <td style="width:50%">
                                    {!!$data["str"]!!}
                                    </td>
                                    <td style="width:50%;">
                                    {!!$data["str"]!!}
                                    </td>
                                </tr>
                            </table>
                @php
                        }
                        else  if($data['page_size'] == "A4")
                        {
                            $page = '<page size="A4" layout="landscape">{!!$data["str"]!!}';
                        }
                        else  if($data['page_size'] == "A4DB")
                        {
                            $page = '<page size="A4">{!!$data["str"]!!}{!!$data["str"]!!}';
                        }
                @endphp
        </div>
        <div class="pagebreak"></div>
        <div class="row">            
            <div class="col-md-12 form-group mt-4">
                <center>
                     <div id="overlay" style="display:none;"><center><p style="margin-top: 273px;color:red;font-weight: 700;">Please do not refresh the page, while the process is going on.</p><img src="http://dev.triz.co.in/admin_dep/images/loader.gif"></center></div>
                    <button class="btn btn-success" id="otherfees">Print Receipt</button> 
                    <input type="hidden" name="action" id="action" value="other_fees_collect_receipt">
                    <input type="hidden" name="last_inserted_ids" id="last_inserted_ids" value="{{$data['last_inserted_ids']}}">
                    <input type="hidden" name="page_size" id="page_size" value="{{$data['page_size']}}">
                    <input type="button" value="Send Email" class="btn btn-success" id="ajax_sendBulkEmail" />
                </center>
            </div>
        </div>


        @endif
    </div>
</div>

@include('includes.footerJs')
<script type="text/javascript">
	window.onafterprint = function(){
      window.location.reload(true);
 	}
    $(document).ready(function(){
        $('#otherfees').on('click', function () 
        {
            $("#overlay").css("display","block");
            var inserted_ids = $("#last_inserted_ids").val();
            var action = $("#action").val();
            var page_size = $("#page_size").val();
            $.ajax({
                    url: '/ajax_PDF_Bulk_OtherFeesReceipt?action='+action+'&inserted_ids='+inserted_ids+'&page_size='+page_size,                
                    success: function(result){ 
                        window.open(result, '_blank');
                        $("#overlay").css("display","none");
                    }
            });
        });
    })
</script>
@include('includes.footer')
