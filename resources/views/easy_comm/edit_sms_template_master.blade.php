@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="card">

            @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>
            </div>
            @endif

            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">

                    <form action="{{ route('sms_template_master.update', $data->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Template Name</label>
                                <input type="text" name="template_name" value="{{ $data->template_name }}" required class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Template ID</label>
                                <input type="text" name="template_id" value="{{ $data->template_id }}" class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Sender ID</label>
                                <input type="text" name="sender_id" value="{{ $data->sender_id }}" class="form-control">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Template Content</label>
                                <textarea name="template_content" required class="form-control" rows="4">{{ old('template_content', $data->template_content ?? '') }}</textarea>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" value="{{ $data->sort_order }}" class="form-control">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Status:</label><br>
                                <input type="radio" name="result_status" value="Y" {{ $data->status == 'Y' ? 'checked' : '' }}> On
                                <input type="radio" name="result_status" value="N" {{ $data->status == 'N' ? 'checked' : '' }}> Off
                            </div>

                            <div class="col-md-12 form-group text-center">
                                <button class="btn btn-success">Update</button>
                                <a href="{{ route('sms_template_master.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger">
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

@include('includes.footerJs')
@include('includes.footer')
