@extends('layouts.app')

@section('title', 'View Company')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Company Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td>{{ $company->id }}</td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td>{{ $company->name }}</td>
                        </tr>
                        <tr>
                            <th>Industry</th>
                            <td>{{ $company->industry }}</td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>{{ $company->address }}</td>
                        </tr>
                        <tr>
                            <th>Contact Email</th>
                            <td>{{ $company->contact_email }}</td>
                        </tr>
                        <tr>
                            <th>Contact Phone</th>
                            <td>{{ $company->contact_phone }}</td>
                        </tr>
                        <tr>
                            <th>Requirements</th>
                            <td>{{ $company->requirements }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection