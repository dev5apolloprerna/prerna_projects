@extends('layouts.app')

@section('title', isset($employee) ? 'Edit Employee' : 'Add Employee')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Alert Messages --}}
            @include('common.alert')

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ isset($employee) ? 'Edit Employee' : 'Add Employee' }}</h4>
                        <div class="page-title-right">
                            <a href="{{ route('employee.index') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ isset($employee) ? route('employee.update', $employee->emp_id) : route('employee.store') }}">
                                @csrf
                                @if(isset($employee))
                                    @method('PUT')
                                @endif

                                <div class="row">
                                    {{-- Name (required) --}}
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Name <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="name" value="{{ old('name', $employee->name ?? '') }}" placeholder="Enter full name">
                                        @if($errors->has('name'))
                                            <span class="text-danger">{{ $errors->first('name') }}</span>
                                        @endif
                                    </div>

                                    {{-- Designation --}}
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Designation</label>
                                        <input type="text" class="form-control" name="designation" value="{{ old('designation', $employee->designation ?? '') }}" placeholder="e.g. Software Engineer">
                                        @if($errors->has('designation'))
                                            <span class="text-danger">{{ $errors->first('designation') }}</span>
                                        @endif
                                    </div>

                                    {{-- Mobile (required) --}}
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Mobile <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="mobile" value="{{ old('mobile', $employee->mobile ?? '') }}" placeholder="e.g. 9876543210">
                                        @if($errors->has('mobile'))
                                            <span class="text-danger">{{ $errors->first('mobile') }}</span>
                                        @endif
                                    </div>
                                    {{-- Daily Basis --}}
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Daily Wages</label>
                                        <input type="text" class="form-control" name="daily_wages" value="{{ old('daily_basis', $employee->daily_wages ?? '') }}" placeholder="e.g. 200">
                                        @if($errors->has('daily_basis'))
                                            <span class="text-danger">{{ $errors->first('daily_basis') }}</span>
                                        @endif
                                    </div>

                                    {{-- Address --}}
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" name="address" value="{{ old('address', $employee->address ?? '') }}" placeholder="Address">
                                        @if($errors->has('address'))
                                            <span class="text-danger">{{ $errors->first('address') }}</span>
                                        @endif
                                    </div>

                                    {{-- Active/Inactive (iStatus) --}}
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Status <span style="color:red;">*</span></label>
                                        <select class="form-select" name="iStatus">
                                            <option value="1" {{ old('iStatus', $employee->iStatus ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('iStatus', $employee->iStatus ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @if($errors->has('iStatus'))
                                            <span class="text-danger">{{ $errors->first('iStatus') }}</span>
                                        @endif
                                    </div>

                                    {{-- NOTE: Per rules, do not ask for iStatus/isDelete/created_at/updated_at fields as inputs.
                                             created_at is shown only in listing. --}}
                                </div>

                                <div class="mt-2">
                                    <button type="submit" class="btn btn-success"> {{ isset($employee) ? 'Update' : 'Save' }}
                                    </button>
                                    @if(isset($employee))
                                        <a href="{{ route('employee.index') }}" class="btn btn-light ms-2">Cancel</a>
                                    @else
                                    <button type="reset" class="btn btn-light">Clear
                                    </button>
                                    @endif
                                </div>
                            </form>
                        </div> {{-- card-body --}}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
