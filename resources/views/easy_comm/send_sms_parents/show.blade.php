@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Send Sms Parents</h4>
            </div>
        </div>
        <div class="card">
            @if (!empty($data['message']))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>{{ $data['message'] }}</strong>
                </div>
            @endif
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <form action="{{ route('send_sms_parents.create') }}" enctype="multipart/form-data" method="post">
                        {{ method_field('GET') }}
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-9 form-group">
                                <div class="row">
                                    {{ App\Helpers\SearchChain('4', 'single', 'grade,std,div') }}
                                </div>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Number Type:</label>
                                <select class="form-control" name="number_type">
                                    <option value="">Select Number</option>
                                    @foreach ($data['number_types'] as $key => $value)
                                        <option value="{{ $key }}"
                                            @if (isset($data['number_type'])) @if ($key == $data['number_type'])
                                                SELECTED @endif
                                            @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <center>
                                    <input type="submit" name="submit" value="Search" class="btn btn-success">
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
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#example').DataTable({});
    });
</script>
@include('includes.footer')
