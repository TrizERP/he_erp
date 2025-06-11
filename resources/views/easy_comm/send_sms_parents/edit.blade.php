@include('../includes.headcss')
<link rel="stylesheet" href="../../../plugins/bower_components/dropify/dist/css/dropify.min.css">
@include('../includes.header')
@include('../includes.sideNavigation')


<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row" style=" margin-top: 25px;">
            <div class="panel-body white-box">
                @if ($message = Session::get('success'))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $message }}</strong>
                </div>
                @endif
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <form action="{{ route('manage_sms_api.update', $data['id']) }}" enctype="multipart/form-data" method="post">
                        {{ method_field("PUT") }}
                        {{csrf_field()}}

                        <div class="col-md-6 form-group">
                            <label>URL</label>
                            <input type="text" id='url' required name="url" value="{{ $data['url'] }}" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Parameter</label>
                            <input type="text" id='parameter' required name="pram" value="{{ $data['pram'] }}" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Mobile Variable</label>
                            <input type="text" id='mobile_var' required name="mobile_var" value="{{ $data['mobile_var'] }}" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Text Variable</label>
                            <input type="text" id='text_var' required name="text_var" value="{{ $data['text_var'] }}" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Last Variable</label>
                            <input type="text" id='last_var' required name="last_var" value="{{ $data['last_var'] }}" class="form-control">
                        </div>


                        <div class="col-md-12 form-group">
                            <center>
                                <input type="submit" name="submit" value="Save" class="btn btn-success" >
                            </center>
                        </div>

                    </form>
                </div>
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>


@include('includes.footerJs')


@include('includes.footer')
