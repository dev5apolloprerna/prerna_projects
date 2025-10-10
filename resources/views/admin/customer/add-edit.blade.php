@extends('layouts.app')

@section('title', isset($customer) ? 'Edit Customer' : 'Add Customer')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Alert Messages --}}
            @include('common.alert')

        <div class="card"> 
          <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ isset($customer) ? 'Edit Customer' : 'Add Customer' }}</h4>
                        <div class="page-title-right">
                            <a href="{{ route('customer.index') }}"
                                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ isset($customer) ? route('customer.update', $customer->customer_id) : route('customer.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Customer Name <span style="color:red;">*</span></label>
                        <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $customer->customer_name ?? '') }}">
                        @if($errors->has('customer_name'))
                            <span class="text-danger">{{ $errors->first('customer_name') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Mobile Number <span style="color:red;">*</span></label>
                        <input type="text" name="customer_mobile" class="form-control" value="{{ old('customer_mobile', $customer->customer_mobile ?? '') }}">
                        @if($errors->has('customer_mobile'))
                            <span class="text-danger">{{ $errors->first('customer_mobile') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Email <span style="color:red;"></span></label>
                        <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $customer->customer_email ?? '') }}">
                        @if($errors->has('customer_email'))
                            <span class="text-danger">{{ $errors->first('customer_email') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Address <span style="color:red;">*</span></label>
                        <textarea name="customer_address" class="form-control">{{ old('customer_address', $customer->customer_address ?? '') }}</textarea>
                        @if($errors->has('customer_address'))
                            <span class="text-danger">{{ $errors->first('customer_address') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Customer Type <span style="color:red;">*</span></label>
                        <select class="form-select" name="customer_type" required>
                            <option value="">Select Customer Type </option>
                             <option value="customer" {{ old('customer_type', $customer->customer_type ?? 'customer') == 'customer' ? 'selected' : '' }}>Customer</option>
                             <option value="retailer" {{ old('customer_type', $customer->customer_type ?? 'retailer') == 'retailer' ? 'selected' : '' }}>Retailer</option>

                        </select>
                        @if($errors->has('customer_type'))
                            <span class="text-danger">{{ $errors->first('customer_type') }}</span>
                        @endif
                    </div>

                </div>

                <button type="submit" class="btn btn-success">
                    {{ isset($customer) ? 'Update' : 'Submit' }}
                </button>
                @if(isset($customer))
                 <a href="{{ route('customer.index') }}" class="btn btn-light">
                    Cancel
                 </a>
                @else
                 <button type="reset" class="btn btn-light">
                    Clear
                </button>
                @endif
            </form>

        </div>
    </div>
 </div>
</div>
</div>
@endsection
