@include('../includes.headcss')
@include('../includes.header')
@include('../includes.sideNavigation')

<div id="page-wrapper">
    <div class="container-fluid">

        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Grade Subject List</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12 text-right">
                <a href="{{ route('grade-subject.create') }}" class="btn btn-success">
                    Add Grade Subject
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ session('success') }}</strong>
            </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Title</th>
                            <th>Breakoff</th>
                            <th>Sort Order</th>
                            <th>Year</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($data as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row->subject_name }}</td>
                                <td>{{ $row->title }}</td>
                                <td>{{ $row->breakoff }}</td>
                                <td>{{ $row->sort_order }}</td>
                                <td>{{ $row->syear }}</td>

                                <td>
                                    <a href="{{ route('grade-subject.edit', $row->id) }}" class="btn btn-warning btn-sm">Edit</a>

                                    <form action="{{ route('grade-subject.destroy', $row->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                    
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this record?')">
                                            Delete
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

@include('includes.footerJs')
@include('includes.footer')
