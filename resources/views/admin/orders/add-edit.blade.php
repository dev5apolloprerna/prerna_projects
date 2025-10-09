@extends('layouts.app')

@section('title', isset($order) ? 'Edit Order' : 'Add Order')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            @include('common.alert')

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ isset($order) ? 'Edit Order' : 'Add Order' }}</h4>
                        <div class="page-title-right">
                            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-primary">
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
                            <form method="POST" action="{{ isset($order) ? route('orders.update', $order->order_id) : route('orders.store') }}">
                                @csrf
                                @if(isset($order)) @method('PUT') @endif

                                <div class="row">
                                    {{-- Order Type --}}
                                    <!-- <div class="col-md-4 mb-4">
                                        <label class="form-label">Order Type <span style="color:red;">*</span></label>
                                        <select class="form-select" name="order_type">
                                            @php $ot = old('order_type', $order->order_type ?? ''); @endphp
                                            <option value="">-- Select --</option>
                                            <option value="monthly" {{ $ot=='monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="quick"   {{ $ot=='quick' ? 'selected' : '' }}>Quick</option>
                                            <option value="daily"   {{ $ot=='daily' ? 'selected' : '' }}>Daily</option>
                                        </select>
                                        @if($errors->has('order_type'))
                                            <span class="text-danger">{{ $errors->first('order_type') }}</span>
                                        @endif
                                    </div> -->

                                    {{-- Rent Type --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Rent Type <span style="color:red;">*</span></label>
                                        <select class="form-select" name="rent_type" id="rent_type">
                                            @php $ot = old('rent_type', $order->rent_type ?? ''); @endphp
                                            <option id="rent_type" value="" >-- Select --</option>
                                            @foreach($renttype as $r)
                                                <option value="{{ $r->rent_price_id }}" {{ (string)old('rent_price_id', $order->rent_type ?? '') === (string)$r->rent_price_id ? 'selected' : '' }}>
                                                    {{ $r->rent_type }}
                                                </option>
                                            @endforeach

                                        </select>
                                        @if($errors->has('rent_type'))
                                            <span class="text-danger">{{ $errors->first('rent_type') }}</span>
                                        @endif
                                    </div>
                                    {{-- Customer --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Customer <span style="color:red;">*</span></label>
                                        <select class="form-select" name="customer_id">
                                            <option value="">-- Select Customer --</option>
                                            @foreach($customers as $cid => $cname)
                                                <option value="{{ $cid }}" {{ (string)old('customer_id', $order->customer_id ?? '') === (string)$cid ? 'selected' : '' }}>
                                                    {{ $cname }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('customer_id'))
                                            <span class="text-danger">{{ $errors->first('customer_id') }}</span>
                                        @endif
                                    </div>

                                    {{-- User --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">User Name <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="user_name"
                                               value="{{ old('user_name', $order->user_name ?? '') }}" placeholder="User Name">
                                        @if($errors->has('user_name'))
                                            <span class="text-danger">{{ $errors->first('user_name') }}</span>
                                        @endif
                                        
                                    </div>

                                    {{-- User --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">User Mobile <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="user_mobile"
                                               value="{{ old('user_mobile', $order->user_mobile ?? '') }}" placeholder="e.g. 9876543210">
                                        @if($errors->has('user_mobile'))
                                            <span class="text-danger">{{ $errors->first('user_mobile') }}</span>
                                        @endif
                                        
                                    </div>

                                    {{-- Tanker --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Tanker <span style="color:red;">*</span></label>
                                        <select class="form-select" name="tanker_id">
                                            <option value="">-- Select Tanker --</option>
                                            @foreach($tankers as $tid => $tno)
                                                <option value="{{ $tid }}" {{ (string)old('tanker_id', $order->tanker_id ?? '') === (string)$tid ? 'selected' : '' }}>
                                                    {{ $tno }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('tanker_id'))
                                            <span class="text-danger">{{ $errors->first('tanker_id') }}</span>
                                        @endif
                                    </div>

                                    

                                    {{-- Rent Start Date/Time --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Rent Start Date <span style="color:red;">*</span></label>
                                        <input type="datetime-local" class="form-control" name="rent_start_date"
                                               value="{{ old('rent_start_date', isset($order) 
                                                    ? \Carbon\Carbon::parse($order->rent_start_date)->format('Y-m-d\TH:i') 
                                                    : now()->format('Y-m-d\TH:i')) }}">
                                        @if($errors->has('rent_start_date'))
                                            <span class="text-danger">{{ $errors->first('rent_start_date') }}</span>
                                        @endif
                                    </div>


                                    {{-- Advance Amount --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Advance Amount <span style="color:red;">*</span></label>
                                        <input type="number" class="form-control" name="advance_amount" min="0"
                                               value="{{ old('advance_amount', $order->advance_amount ?? '') }}" placeholder="0">
                                        @if($errors->has('advance_amount'))
                                            <span class="text-danger">{{ $errors->first('advance_amount') }}</span>
                                        @endif
                                    </div>

                                    {{-- Rent Amount --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Rent Amount <span style="color:red;">*</span></label>
                                        <input type="number" class="form-control" name="rent_amount" min="0"
                                               value="{{ old('rent_amount', $order->rent_amount ?? '') }}" placeholder="0">
                                        @if($errors->has('rent_amount'))
                                            <span class="text-danger">{{ $errors->first('rent_amount') }}</span>
                                        @endif
                                    </div>

                                    {{-- Reference Name --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Reference Name <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="reference_name"
                                               value="{{ old('reference_name', $order->reference_name ?? '') }}" placeholder="Reference Name">
                                        @if($errors->has('reference_name'))
                                            <span class="text-danger">{{ $errors->first('reference_name') }}</span>
                                        @endif
                                    </div>

                                    {{-- Reference Mobile --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Reference Mobile <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="reference_mobile_no"
                                               value="{{ old('reference_mobile_no', $order->reference_mobile_no ?? '') }}" placeholder="e.g. 9876543210">
                                        @if($errors->has('reference_mobile_no'))
                                            <span class="text-danger">{{ $errors->first('reference_mobile_no') }}</span>
                                        @endif
                                    </div>

                                    {{-- Reference Address --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Reference Address <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="reference_address"
                                               value="{{ old('reference_address', $order->reference_address ?? '') }}" placeholder="Reference Address">
                                        @if($errors->has('reference_address'))
                                            <span class="text-danger">{{ $errors->first('reference_address') }}</span>
                                        @endif
                                    </div>

                                    {{-- Tanker Location --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Tanker Location <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="tanker_location"
                                               value="{{ old('tanker_location', $order->tanker_location ?? '') }}" placeholder="Location">
                                        @if($errors->has('tanker_location'))
                                            <span class="text-danger">{{ $errors->first('tanker_location') }}</span>
                                        @endif
                                    </div>


                                <div class="col-md-4 mb-4">
                                        <label class="form-label">Contract <span style="color:red;">*</span></label>
                                        <textarea  class="form-control" name="contract_text">{{ old('contract_text', $order->contract_text ?? '') }}</textarea>
                                        @if($errors->has('contract_text'))
                                            <span class="text-danger">{{ $errors->first('contract_text') }}</span>
                                        @endif
                                    </div>

                                    {{-- Status --}}
                                    <div class="col-md-4 mb-4">
                                        <label class="form-label">Status <span style="color:red;">*</span></label>
                                        <select class="form-select" name="iStatus">
                                            <option value="1" {{ old('iStatus', $order->iStatus ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('iStatus', $order->iStatus ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @if($errors->has('iStatus'))
                                            <span class="text-danger">{{ $errors->first('iStatus') }}</span>
                                        @endif
                                    </div>

                                </div>

                                <div class="mt-2">
                                    <button type="submit" class="btn btn-success">
                                        {{ isset($order) ? 'Update' : 'Save' }}
                                    </button>
                                    @if(isset($order))
                                    <a href="{{ route('orders.index') }}" class="btn btn-light ms-2">Cancel</a>
                                    @else
                                    <button type="reset" class="btn btn-light">
                                    <i class="far fa-save"></i> Clear
                                    </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rentTypeEl  = document.getElementById('rent_type');
  const amountEl    = document.getElementById('rent_amount');

  async function fetchPriceAndSet() {
    const rt = rentTypeEl.value;
    if (!rt) { amountEl.value = ''; return; }

    try {
      const url = `{{ route('ajax.rent-price') }}?rent_type=${encodeURIComponent(rt)}`;
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      const data = await res.json();

      if (res.ok && data.ok && typeof data.amount !== 'undefined') {
        amountEl.value = data.amount; // overwrite on every change
      } else {
        amountEl.value = '';
        console.warn(data.message || 'Price not found');
        // Optional: show a toast/alert if you want
      }
    } catch (e) {
      console.error(e);
      amountEl.value = '';
    }
  }

  rentTypeEl.addEventListener('change', fetchPriceAndSet);

  // Auto-fill on page load if rent_type is preselected (e.g., edit)
  if (rentTypeEl.value && !amountEl.value) {
    fetchPriceAndSet();
  }
});
</script>
@endsection
