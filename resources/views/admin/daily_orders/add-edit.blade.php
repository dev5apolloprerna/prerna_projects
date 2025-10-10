@php
  /** @var \Illuminate\Support\Collection|\App\Models\Customer[] $customers */
  // When $order is present, prefill; else defaults are empty/new
  $isEdit   = isset($order) && $order;
  $action   = $isEdit ? route('daily-orders.update', $order->daily_order_id) : route('daily-orders.store');
  $method   = $isEdit ? 'PUT' : 'POST';
  // Decide initial customer type: retail if customer_id=0
  $initialType = old('customer_type',
      $isEdit
        ? ((int)($order->customer_id ?? 0) === 0 ? 'retail' : 'recurring')
        : 'recurring'
  );
  $selectedCustomerId = old('customer_id', $isEdit ? ($order->customer_id ?? '') : '');
@endphp

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
                        <h4 class="mb-sm-0">{{ $isEdit ? 'Edit Daily Order' : 'Add Daily Order' }}</h4>
                        <div class="page-title-right">
                        	<button type="button" id="btnCancelEdit" class="btn btn-sm btn-outline-secondary {{ $isEdit ? '' : 'd-none' }}">Cancel Edit</button>

                            <a href="{{ route('daily-orders.index') }}"
                                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>


    <form method="POST" id="orderForm" action="{{ $action }}">
      @csrf
      <div class="row">
      <input type="hidden" name="_method" id="formMethod" value="{{ $method }}">
      <input type="hidden" name="edit_id" id="edit_id" value="{{ $isEdit ? $order->daily_order_id : '' }}">
      <input type="hidden" name="customer_id" id="customer_id"
             value="{{ $initialType === 'retail' ? 0 : ($selectedCustomerId ?: '') }}">

      {{-- Customer Type --}}
      <div class="col-md-12 mb-3">
        <label class="form-label d-block">Customer Type <span class="text-danger">*</span></label>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="customer_type" id="type_recurring" value="recurring"
                 {{ $initialType === 'recurring' ? 'checked' : '' }}>
          <label class="form-check-label" for="type_recurring">Recurring</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="customer_type" id="type_retail" value="retail"
                 {{ $initialType === 'retail' ? 'checked' : '' }}>
          <label class="form-check-label" for="type_retail">Retail</label>
        </div>
      </div>

      {{-- Recurring: dropdown (only shown in recurring) --}}
      <div id="recurringWrap" class="col-md-6 mb-3 {{ $initialType === 'retail' ? 'd-none' : '' }}">
        <label class="form-label">Select Customer <span class="text-danger">*</span></label>
        <select id="customer_id_select" class="form-select">
          <option value="">-- Select --</option>
          @foreach($customers as $c)
            <option value="{{ $c->customer_id }}"
                    data-name="{{ $c->customer_name }}"
                    data-mobile="{{ $c->customer_mobile }}"
                    {{ (string)$selectedCustomerId === (string)$c->customer_id ? 'selected' : '' }}>
              {{ $c->customer_name }} — {{ $c->customer_mobile }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Name (visible for both — editable) --}}
      <div class="col-md-6 mb-3">
        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
        <input type="text" name="customer_name" id="customer_name" class="form-control"
               placeholder="Customer name"
               value="{{ old('customer_name', $isEdit ? $order->customer_name : '') }}" required>
      </div>

      {{-- Mobile (visible for both — editable) --}}
      <div class="col-md-6 mb-3">
        <label class="form-label">Mobile <span class="text-danger">*</span></label>
        <input type="text" name="mobile" id="mobile" class="form-control"
               placeholder="10-digit mobile" inputmode="numeric" pattern="[0-9]{10,15}"
               value="{{ old('mobile', $isEdit ? $order->mobile : '') }}" required>
        <small class="text-muted">Digits only (10–15)</small>
      </div>

      {{-- Location --}}
      <div class="col-md-6 mb-3">
        <label class="form-label">Location <span class="text-danger">*</span></label>
        <input type="text" name="location" id="location" class="form-control"
               value="{{ old('location', $isEdit ? $order->location : '') }}" required>
      </div>

      {{-- Rent Date / Service Type --}}
      <div class="row g-2">
        <div class="col-md-6">
          <label class="form-label">Rent Date <span class="text-danger">*</span></label>
          <input type="date" name="rent_date" id="rent_date" class="form-control"
                 value="{{ old('rent_date', $isEdit ? \Illuminate\Support\Str::of($order->rent_date)->substr(0,10) : '') }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Service Type <span class="text-danger">*</span></label>
          <input type="text" name="service_type" id="service_type" class="form-control"
                 placeholder="e.g. Tanker"
                 value="{{ old('service_type', $isEdit ? $order->service_type : '') }}" required>
        </div>
      </div>

      {{-- Amount / Status --}}
      <div class="row g-2 mt-2">
        <div class="col-md-6">
          <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
          <input type="number" min="0" name="amount" id="amount" class="form-control"
                 value="{{ old('amount', $isEdit ? $order->amount : '') }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select name="iStatus" id="iStatus" class="form-select">
            <option value="1" {{ (string)old('iStatus', $isEdit ? $order->iStatus : 1) === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ (string)old('iStatus', $isEdit ? $order->iStatus : 1) === '0' ? 'selected' : '' }}>Inactive</option>
          </select>
        </div>
      </div>

      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary" id="btnSubmit">{{ $isEdit ? 'Update' : 'Save' }}</button>
        <button type="reset" class="btn btn-light" id="btnReset">Reset</button>
      </div>
  </div>
    </form>
  </div>
</div>
</div>
</div>
</div>

@endsection
@section('scripts')
<script>
(function(){
  // Form + controls (these exist inside the included partial)
  const form          = document.getElementById('orderForm');
  const formTitle     = document.getElementById('formTitle');
  const btnCancelEdit = document.getElementById('btnCancelEdit');
  const formMethod    = document.getElementById('formMethod');
  const editId        = document.getElementById('edit_id');

  const typeRecurring = document.getElementById('type_recurring');
  const typeRetail    = document.getElementById('type_retail');

  const recurringWrap = document.getElementById('recurringWrap');
  const customerIdInp = document.getElementById('customer_id');
  const customerSel   = document.getElementById('customer_id_select');

  const nameInp   = document.getElementById('customer_name');
  const mobileInp = document.getElementById('mobile');

  function setTypeUI(mode){
    if(mode === 'retail'){
      recurringWrap.classList.add('d-none');
      customerIdInp.value = 0; // retail marker
      // keep name/mobile as typed
    } else {
      recurringWrap.classList.remove('d-none');
      // if something selected, sync fields
      syncFromSelect(/*overwriteEmptyOnly*/ true);
    }
  }

  function syncFromSelect(overwriteEmptyOnly = true){
    const opt = customerSel.options[customerSel.selectedIndex];
    if(!opt || !opt.value){
      customerIdInp.value = '';
      return;
    }
    customerIdInp.value = opt.value;
    const nm = opt.getAttribute('data-name') || '';
    const mb = opt.getAttribute('data-mobile') || '';
    if(!overwriteEmptyOnly || nameInp.value.trim() === '') nameInp.value = nm;
    if(!overwriteEmptyOnly || mobileInp.value.trim() === '') mobileInp.value = mb;
  }

  // Init based on the currently checked radio (supports server-side prefill too)
  const initialMode = typeRetail.checked ? 'retail' : 'recurring';
  setTypeUI(initialMode);

  // Radio change
  typeRecurring.addEventListener('change', ()=> setTypeUI('recurring'));
  typeRetail.addEventListener('change',    ()=> setTypeUI('retail'));

  // Dropdown change
  customerSel.addEventListener('change', ()=> syncFromSelect(false));

  // Submit guard
  form.addEventListener('submit', (e) => {
    const mode = typeRetail.checked ? 'retail' : 'recurring';
    if(mode === 'recurring'){
      if(!customerIdInp.value){
        e.preventDefault();
        alert('Please select a Recurring customer.');
        return false;
      }
    }
    if(nameInp.value.trim() === '' || mobileInp.value.trim() === ''){
      e.preventDefault();
      alert('Please enter customer name and mobile.');
      return false;
    }
  });

  // Edit buttons (fill form on same page)
  document.querySelectorAll('.btnEdit').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      formTitle.textContent = 'Edit Daily Order';
      btnCancelEdit.classList.remove('d-none');
      form.action = "{{ route('daily-orders.update', ':id') }}".replace(':id', btn.dataset.id);
      formMethod.value = 'PUT';
      editId.value = btn.dataset.id;

      const isRetail = btn.dataset.type === 'retail';
      if(isRetail){
        typeRetail.checked = true;
        setTypeUI('retail');
        customerSel.value = '';
        customerIdInp.value = 0;
      }else{
        typeRecurring.checked = true;
        setTypeUI('recurring');
        customerSel.value = btn.dataset.customer_id || '';
        customerIdInp.value = btn.dataset.customer_id || '';
        // Ensure name/mobile align with selected customer if empty
        syncFromSelect(true);
      }

      nameInp.value   = btn.dataset.customer_name || '';
      mobileInp.value = btn.dataset.mobile || '';

      document.getElementById('location').value     = btn.dataset.location || '';
      document.getElementById('rent_date').value    = btn.dataset.rent_date || '';
      document.getElementById('service_type').value = btn.dataset.service_type || '';
      document.getElementById('amount').value       = btn.dataset.amount || '';
      document.getElementById('iStatus').value      = btn.dataset.istatus || '1';

      window.scrollTo({top: 0, behavior: 'smooth'});
    });
  });

  // Cancel edit -> back to add
  btnCancelEdit.addEventListener('click', ()=>{
    formTitle.textContent = 'Add Daily Order';
    btnCancelEdit.classList.add('d-none');
    form.action = "{{ route('daily-orders.store') }}";
    formMethod.value = 'POST';
    editId.value = '';
    form.reset();
    customerSel.value = '';
    typeRecurring.checked = true;
    setTypeUI('recurring');
  });
})();
</script>
@endsection
