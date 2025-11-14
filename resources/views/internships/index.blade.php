@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Internship List</h3>
                    <div class="card-tools">
                        <a href="{{ route('internships.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Company</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($internships as $internship)
                            <tr>
                                <td>{{ $internship->id }}</td>
                                <td>{{ $internship->title }}</td>
                                <td>{{ $internship->company->name }}</td>
                                <td>{{ $internship->start_date->format('d M Y') }}</td>
                                <td>{{ $internship->end_date->format('d M Y') }}</td>
                                <td>
                                    @if($internship->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('internships.show', $internship->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('internships.edit', $internship->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('internships.students.index', $internship->id) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <form action="{{ route('internships.destroy', $internship->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $internships->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@include('includes.footerJs')
@include('includes.footer')