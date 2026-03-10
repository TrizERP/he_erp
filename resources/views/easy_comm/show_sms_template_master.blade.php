@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="card">

            @if(session('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ session('success') }}</strong>
            </div>
            @endif

            <div class="row">
                <div class="col-lg-3">
                    <a href="{{ route('sms_template_master.create') }}"
                       class="btn btn-info add-new">
                        <i class="fa fa-plus"></i> Add New
                    </a>
                </div>

                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Template Name</th>
                                    <th>Template ID</th>
                                    <th>Sender ID</th>
                                    <th>Template Content</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th class="text-align">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['data'] as $row)
                                <tr>
                                    <td>{{ $row->template_name }}</td>
                                    <td>{{ $row->template_id }}</td>
                                    <td>{{ $row->sender_id }}</td>
                                    <td>{{ $row->template_content }}</td>
                                    <td>{{ $row->sort_order }}</td>
                                    <td>{{ $row->status }}</td>
                                    <td>
                                        <a href="{{ route('sms_template_master.edit', $row->id) }}"
                                           class="btn btn-outline-success">
                                            <i class="ti-pencil-alt"></i>
                                        </a>

                                        <form action="{{ route('sms_template_master.destroy', $row->id) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger"
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@include('includes.footerJs')
<script>
$(document).ready(function () {
    $('#example').DataTable({
        order: [[4, 'asc']] // column index starts from 0
    });
});
</script>
@include('includes.footer')
