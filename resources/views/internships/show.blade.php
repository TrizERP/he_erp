@include('includes.headcss')
@include('includes.header')
@include('includes.sideNavigation')
<div id="page-wrapper">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Internship Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('internships.edit', $internship->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td>{{ $internship->id }}</td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td>{{ $internship->title }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $internship->description }}</td>
                        </tr>
                        <tr>
                            <th>Company</th>
                            <td>{{ $internship->company->name }}</td>
                        </tr>
                        <tr>
                            <th>Start Date</th>
                            <td>{{ $internship->start_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th>End Date</th>
                            <td>{{ $internship->end_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($internship->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-12">
                            <a href="{{ route('internships.students.index', $internship->id) }}" class="btn btn-success">
                                <i class="fas fa-users"></i> Manage Students
                            </a>
                            <a href="{{ route('internships.shifts.index', $internship->id) }}" class="btn btn-info">
                                <i class="fas fa-clock"></i> Manage Shifts
                            </a>
                            <a href="{{ route('internships.holidays.index', $internship->id) }}" class="btn btn-warning">
                                <i class="fas fa-calendar"></i> Manage Holidays
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@include('includes.footerJs')
@include('includes.footer')