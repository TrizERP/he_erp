@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Company</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('companies.update', $company->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">Company Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $company->name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="industry">Industry</label>
                            <input type="text" class="form-control" id="industry" name="industry" value="{{ $company->industry }}" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required>{{ $company->address }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="{{ $company->contact_email }}" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="{{ $company->contact_phone }}" required>
                        </div>
                        <div class="form-group">
                            <label for="requirements">Requirements</label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="3">{{ $company->requirements }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection