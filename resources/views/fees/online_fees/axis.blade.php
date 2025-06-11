@include('../includes.headcss')
@include('../includes.header')
@include('../includes.sideNavigation')


<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">            
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">                
                <h4 class="page-title">Payment Mapping</h4>            
            </div>                    
        </div>
        <div class="card">
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <form action="{{ route('online_fees.store') }}" enctype="multipart/form-data" method="post">
                    {{ method_field("POST") }}
                    @csrf
                        <input type="hidden" name="map_company" value="axis">
                        <div class="row">                            
                            <div class="col-md-3 form-group">
                                <label>ENC KEY</label>
                                <input type="text" id='encryption_key' required name="encryption_key" class="form-control">
                            </div>  
                            <div class="col-md-3 form-group">
                                <label>Checksum Key</label>
                                <input type="text" id='checksum_key' required name="checksum_key" class="form-control">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Cid</label>
                                <input type="text" id='cid' required name="cid" class="form-control">
                            </div>
                            <!-- <div class="col-md-6 form-group">
                                <label>Merchant ID</label>
                                <input type="text" id='merchant_id' required name="merchant_id" class="form-control">
                            </div> -->
                            <div class="col-md-3 form-group">
                                <label>Fees Collect Type</label>
                                <div class="radio radio-info mt-2">
                                    <input type="radio" name="fees_type" value="fix" checked><label for="Fix"> Fix </label>
                                    <input type="radio" name="fees_type" value="dynamic"><label for="Dynamic"> Dynamic </label>
                                </div>
                            </div>                        
                            <div class="col-md-12 form-group">
                                <center>                                    
                                    <input type="submit" name="submit" value="Save" class="btn btn-success" >
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
@include('includes.footer')
