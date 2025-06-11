{{--
@include('includes.headcss')
--}}
@extends('layout')
@section('container')
<link rel="stylesheet" href="../../../plugins/bower_components/dropify/dist/css/dropify.min.css">
{{--@include('includes.header')
@include('includes.sideNavigation')--}}

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Edit Fees Config</h4>
            </div>
        </div>
        <div class="card">
			<!-- @TODO: Create a saperate tmplate for messages and include in all tempate -->
            @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>
            </div>
            @endif
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <form action="{{ route('fees_config_master.update', $data['id']) }}" enctype="multipart/form-data" method="post">
                        {{ method_field("PUT") }}
                        @csrf
                        <div class="row">
	                        <div class="col-md-4 form-group">
	                            <label>Late Fees Amount </label>
	                            <input type="number" id='late_fees_amount' value="@if(isset($data['late_fees_amount'])){{ $data['late_fees_amount'] }}@endif" required name="late_fees_amount" class="form-control">
	                        </div>

	                        <div class="col-md-4 form-group">
	                            <label>Fees Paid Send SMS</label>
	                            <select name="send_sms" id="send_sms" class="form-control" required>
	                                <option value=""> Select Send Sms </option>
	                                <option value="1" @if(isset($data))@if("1" == $data['send_sms']) selected @endif  @endif> Yes </option>
	                                <option value="0" @if(isset($data))@if("0" == $data['send_sms']) selected @endif  @endif> No. </option>
	                            </select>
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>Fees Paid Send Email</label>
	                            <select name="send_email" id="send_email" class="form-control" required>
	                                <option value=""> Select Send Email </option>
	                                <option value="1" @if(isset($data))@if("1" == $data['send_email']) selected @endif  @endif> Yes </option>
	                                <option value="0" @if(isset($data))@if("0" == $data['send_email']) selected @endif  @endif> No. </option>
	                            </select>
	                        </div>

	                        <div class="col-md-4 form-group">
	                            <label>Fees Receipt Template</label>
	                            <select name="fees_receipt_template" id="fees_receipt_template" class="form-control" required>
	                                <option value=""> Select Receipt Template </option>
	                                <option value="A5" @if(isset($data))@if("A5" == $data['fees_receipt_template']) selected @endif  @endif> A5 </option>
	                                <option value="A5DB" @if(isset($data))@if("A5DB" == $data['fees_receipt_template']) selected @endif  @endif> A5 Double </option>
	                                <option value="A4" @if(isset($data))@if("A4" == $data['fees_receipt_template']) selected @endif  @endif> A4 </option>
	                                <option value="A4DB" @if(isset($data))@if("A4DB" == $data['fees_receipt_template']) selected @endif  @endif> A4 Double </option>
	                            </select>
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>Fees Bank Challan Template</label>
	                            <select name="fees_bank_challan_template" id="fees_bank_challan_template" class="form-control" required>
	                                <option value=""> Select Bank Challan Template </option>
	                                <option value="template_1" @if(isset($data))@if("template_1" == $data['fees_bank_challan_template']) selected @endif  @endif> Template 1 </option>
	                                <option value="template_2" @if(isset($data))@if("template_2" == $data['fees_bank_challan_template']) selected @endif  @endif> Template 2 </option>
	                                <option value="template_3" @if(isset($data))@if("template_3" == $data['fees_bank_challan_template']) selected @endif  @endif> Template 3 </option>
	                                <option value="template_4" @if(isset($data))@if("template_4" == $data['fees_bank_challan_template']) selected @endif  @endif> Template 4 </option>
	                            </select>
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>Fees Receipt Note</label>
	                            <textarea placeholder="Please write fees notes"  id='fees_receipt_note' required name="fees_receipt_note" class="form-control">@if(isset($data['fees_receipt_note'])){{ $data['fees_receipt_note'] }}@endif</textarea>
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>Institute Name </label>
	                            <input type="text" id='institute_name' value="@if(isset($data['institute_name'])){{ $data['institute_name'] }}@endif" required name="institute_name" class="form-control">
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>Pan No. </label>
	                            <input type="text" id='pan_no' value="@if(isset($data['pan_no'])){{ $data['pan_no'] }}@endif" required name="pan_no" class="form-control">
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>Account To Be Credited </label>
	                            <input type="text" id='account_to_be_credited' value="@if(isset($data['account_to_be_credited'])){{ $data['account_to_be_credited'] }}@endif" required name="account_to_be_credited" class="form-control">
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>CMS Client Code </label>
	                            <input type="text" id='cms_client_code' value="@if(isset($data['cms_client_code'])){{ $data['cms_client_code'] }}@endif" required name="cms_client_code" class="form-control">
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>Auto Head Counting </label>
	                            @php
                                    $checked = '';
                                    if(isset ($data['auto_head_counting']) && $data['auto_head_counting'] == 1 )
                                    {
                                       	$checked = 'checked';
                                    }
                                @endphp
	                            <input {{$checked}} type="checkbox" id='auto_head_counting' value="1" name="auto_head_counting">
	                        </div>
							<div class="col-md-4 form-group">
	                            <label>Month Beside Fees Heading </label>
	                            @php
                                    $checked = '';
                                    if(isset ($data['show_month']) && $data['show_month'] == 1 )
                                    {
                                       	$checked = 'checked';
                                    }
                                @endphp
	                            <input {{$checked}} type="checkbox" id='show_month' value="1" name="show_month">
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>NACH Account Type</label>
	                            <select name="nach_account_type" id="nach_account_type" class="form-control" required>
	                                <option value=""> Select Account Type </option>
	                                <option value="saving" @if(isset($data))@if("saving" == $data['nach_account_type']) selected @endif  @endif> Saving Account </option>
	                                <option value="current" @if(isset($data))@if("current" == $data['nach_account_type']) selected @endif  @endif> Current Account </option>
	                                <option value="cash" @if(isset($data))@if("cash" == $data['nach_account_type']) selected @endif  @endif> Cash / Credit </option>
	                            </select>
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>NACH Registration Charge </label>
	                            <input type="number" id='nach_registration_charge' value="@if(isset($data['nach_registration_charge'])){{ $data['nach_registration_charge'] }}@endif" required name="nach_registration_charge" class="form-control">
	                        </div>
	                        <div class="col-md-4 form-group">
	                            <label>NACH Transaction Charge </label>
	                            <input type="number" id='nach_transaction_charge' value="@if(isset($data['nach_transaction_charge'])){{ $data['nach_transaction_charge'] }}@endif" required name="nach_transaction_charge" class="form-control">
	                        </div>
	                        <div class="col-md-4 form-group ml-0 mr-0">
	                            <label>NACH Failed Charge </label>
	                            <input type="number" id='nach_failed_charge' value="@if(isset($data['nach_failed_charge'])){{ $data['nach_failed_charge'] }}@endif" required name="nach_failed_charge" class="form-control">
	                        </div>
	                        <div class="col-md-4 form-group ml-0">
	                            <label for="input-file-now">Bank Logo</label>
	                            <input type="file" accept="image/*" @if(isset($data))data-default-file="/storage/fees/{{ $data['bank_logo'] }}" @else required @endif name="fees_bank_logo" id="input-file-now" class="dropify" />
	                        </div>
	                        <div class="col-md-12 form-group">
	                        	<center>
	                                <input type="submit" name="submit" value="Update" class="btn btn-success" >
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
<script src="../../../plugins/bower_components/dropify/dist/js/dropify.min.js"></script>
<script>
$(document).ready(function() {
    // Basic
    $('.dropify').dropify();
    // Translated
    $('.dropify-fr').dropify({
        messages: {
            default: 'Glissez-déposez un fichier ici ou cliquez',
            replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
            remove: 'Supprimer',
            error: 'Désolé, le fichier trop volumineux'
        }
    });
    // Used events
    var drEvent = $('#input-file-events').dropify();
    drEvent.on('dropify.beforeClear', function(event, element) {
        return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
    });
    drEvent.on('dropify.afterClear', function(event, element) {
        alert('File deleted');
    });
    drEvent.on('dropify.errors', function(event, element) {
        console.log('Has Errors');
    });
    var drDestroy = $('#input-file-to-destroy').dropify();
    drDestroy = drDestroy.data('dropify')
    $('#toggleDropify').on('click', function(e) {
        e.preventDefault();
        if (drDestroy.isDropified()) {
            drDestroy.destroy();
        } else {
            drDestroy.init();
        }
    })
});
</script>
@include('includes.footer')
@endsection
