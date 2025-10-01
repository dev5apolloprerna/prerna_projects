@extends('layouts.app')

@section('title', isset($vendor) ? 'Edit Vendor' : 'Add Vendor')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            @include('common.alert')

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ isset($vendor) ? 'Edit Vendor' : 'Add Vendor' }}</h4>
                        <div class="page-title-right">
                            <a href="{{ route('vendor.index') }}" class="btn btn-sm btn-primary">
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
                            <form method="POST" action="{{ isset($vendor) ? route('vendor.update', $vendor->vendor_id) : route('vendor.store') }}">
                                @csrf
                                @if(isset($vendor)) @method('PUT') @endif

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Vendor Name <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="vendor_name" value="{{ old('vendor_name', $vendor->vendor_name ?? '') }}" placeholder="Vendor/Company Name">
                                        @if($errors->has('vendor_name'))
                                            <span class="text-danger">{{ $errors->first('vendor_name') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Contact Person</label>
                                        <input type="text" class="form-control" name="contact_person" value="{{ old('contact_person', $vendor->contact_person ?? '') }}" placeholder="Contact Person">
                                        @if($errors->has('contact_person'))
                                            <span class="text-danger">{{ $errors->first('contact_person') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="{{ old('email', $vendor->email ?? '') }}" placeholder="email@example.com">
                                        @if($errors->has('email'))
                                            <span class="text-danger">{{ $errors->first('email') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Mobile</label>
                                        <input type="text" class="form-control" name="mobile" value="{{ old('mobile', $vendor->mobile ?? '') }}" placeholder="10-15 digits">
                                        @if($errors->has('mobile'))
                                            <span class="text-danger">{{ $errors->first('mobile') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" name="address" value="{{ old('address', $vendor->address ?? '') }}" placeholder="Address">
                                        @if($errors->has('address'))
                                            <span class="text-danger">{{ $errors->first('address') }}</span>
                                        @endif
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">GST Number</label>
                                        <input type="text" class="form-control" name="gst_number" value="{{ old('gst_number', $vendor->gst_number ?? '') }}" placeholder="GSTIN">
                                        @if($errors->has('gst_number'))
                                            <span class="text-danger">{{ $errors->first('gst_number') }}</span>
                                        @endif
                                    </div>

                                    {{-- Active/Inactive --}}
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Status <span style="color:red;">*</span></label>
                                        <select class="form-select" name="iStatus">
                                            <option value="1" {{ old('iStatus', $vendor->iStatus ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('iStatus', $vendor->iStatus ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @if($errors->has('iStatus'))
                                            <span class="text-danger">{{ $errors->first('iStatus') }}</span>
                                        @endif
                                    </div>

                                    {{-- Per rules: do NOT ask for created_at/updated_at/iStatus(is shown via select)/isDelete/vendor_id input --}}
                                </div>

                                <div class="mt-2">
                                    <button type="submit" class="btn btn-success">
                                         {{ isset($vendor) ? 'Update' : 'Save' }}
                                    </button>
                                     @if(isset($vendor))
                                        <a href="{{ route('vendor.index') }}" class="btn btn-secondary ms-2">Cancel</a>
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
