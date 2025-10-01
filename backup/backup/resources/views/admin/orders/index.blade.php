@extends('layouts.app')

@section('title', 'Orders')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Alerts --}}
            @include('common.alert')
        <div class="card">
             <div class="card-header">
            <h5> Order Listing
            </h5>
          </div>
          <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-10">
                    <form method="GET" action="{{ route('orders.index') }}" class="d-flex">
                        <input type="text" class="form-control me-2" name="search" value="{{ request('search') }}"
                               placeholder="Order Type / Rent Type / Ref Name / Ref Mobile / Location">
                        <select class="form-select me-2" name="rent_type">
                            <option value="">-- Rent Type --</option>
                            <option value="daily"   {{ request('rent_type') == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="monthly" {{ request('rent_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                        <select class="form-select me-2" name="isReceive">
                            <option value="">-- Tanker Status --</option>
                            <option value="1"   {{ request('isReceive') == '1' ? 'selected' : '' }}>Not Received</option>
                            <option value="0" {{ request('isReceive') == '0' ? 'selected' : '' }}>Received</option>
                        </select>
                        <button type="submit" class="btn btn-primary me-2">Search</button>
                        <a href="{{ route('orders.index') }}" class="btn btn-light">Reset</a>
                    </form>
                </div>
                <div class="col-md-2 text-end">
                    <a href="{{ route('orders.create') }}" class="btn btn-sm btn-primary">
                        <i class="far fa-plus"></i> Add New
                    </a>
                </div>
            </div>

            {{-- Top bar: bulk delete + search --}}
            <div class="row mb-3">
                <div class="col-md-8">
                    <button id="btnBulkDelete" class="btn btn-sm btn-danger">
                        <i class="far fa-trash-alt"></i> Bulk Delete
                    </button>
                </div>
               
                <div class="col-md-2">
                    <div class="text-end mt-2">
                    <span class="badge bg-success me-2">Total Paid: {{ $totalPaid }}</span>
                    <span class="badge bg-danger me-2">Total Unpaid: {{ $totalUnpaid }} </span>
                    </div>
                </div>
                
                
            </div>

            
                    <div class="table-responsive">
                        <table class="table align-middle table-striped">
                            <thead>
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" id="checkAll"></th>
                                    <th>Order Type</th>
                                    <th>Customer</th>
                                    <th>Tanker No</th>
                                    <th>Tanker Name</th>
                                    <th>Rent Start</th>
                                    <!-- <th>Advance</th>
                                    <th>Rent</th>
                                    <th>Ref. Name</th>
                                    <th>Ref. Mobile</th> -->
                                    <th>Tanker Location</th>
                                    <!-- <th>Created At</th> {{-- must be shown --}} -->
                                    <th>Rent</th>
                                    <th>M/D</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Unpaid</th>
                                    <th>Tanker Status</th>
                                    <th style="width:110px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $o)
                                @php
                                    $snap = $o->dueSnapshot();
                                  @endphp
                                    <tr data-id="{{ $o->order_id }}">
                                        <td><input type="checkbox" class="row-check" value="{{ $o->order_id }}"></td>
                                        <td>{{ ucfirst($o->order_type) }}</td>
                                        <td>{{ $o->customer->customer_name ?? $o->customer_id }}</td>
                                        <td>{{ $o->tanker->tanker_code ?? '-' }}</td>
                                        <td>{{ $o->tanker->tanker_name ?? '-' }}</td>
                                        <!-- <td>{{ ucfirst($o->rent_type) }}</td> -->
                                        <td>{{ \Carbon\Carbon::parse($o->rent_start_date)->format('d-m-Y') }}</td>
                                        <!-- <td>{{ number_format($o->advance_amount) }}</td>
                                        <td>{{ number_format($o->rent_amount) }}</td>
                                        <td>{{ $o->reference_name }}</td>
                                        <td>{{ $o->reference_mobile_no }}</td> -->
                                        <td>{{ $o->tanker_location }}</td>
                                        <!-- <td>{{ \Carbon\Carbon::parse($o->created_at)->format('d M Y H:i') }}</td> -->
                                        
                                          <td>₹{{ number_format($snap['base']) }}</td>
                                        <td>
                                          <!--<strong>₹{{ number_format($snap['total_due']) }}</strong>-->
                                          <div class="small text-muted">
                                            @if($snap['rent_basis'] === 'daily')
                                              ({{ $snap['days_used'] }} day{{ $snap['days_used'] > 1 ? 's' : '' }})
                                            @else
                                              ({{ $snap['months'] }} month{{ $snap['months'] > 1 ? 's' : '' }})
                                            @endif
                                          </div>
                                        </td>
                                          <td><strong>₹{{ number_format($snap['total_due']) }}</strong></td>
                                          <td>₹{{ number_format($snap['paid_sum']) }}</td>
                                          <td class="{{ $snap['unpaid']>0 ? 'text-danger fw-bold' : '' }}">
                                            ₹{{ number_format($snap['unpaid']) }}
                                          </td>

                                        <td>
                                          @php
                                              // If isReceive = 1 (currently Not Received) → next state is RECEIVED
                                              $confirmMsg = $o->isReceive == 1
                                                  ? "Are you sure you want to mark as RECEIVED?"
                                                  : "Are you sure you want to mark as NOT RECEIVED?";
                                            @endphp

                                            @if($o->isReceive == 1)
                                              <a href="{{ route('orders.toggle-receive', $o->order_id) }}"
                                                 class="btn btn-sm btn-danger"
                                                 onclick="return confirm('{{ $confirmMsg }}')">
                                                 Not Received
                                              </a>
                                            @else
                                              <a href="{{ route('orders.toggle-receive', $o->order_id) }}"
                                                 class="btn btn-sm btn-success"
                                                 onclick="return confirm('{{ $confirmMsg }}')">
                                                 Received
                                              </a>
                                            @endif

                                        </td>
                                       <!--  <td>
                                            <span class="badge bg-{{ $o->iStatus ? 'success' : 'secondary' }} toggle-status"
                                                  style="cursor:pointer" data-id="{{ $o->order_id }}">
                                                {{ $o->iStatus ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td> -->
                                        <td>
                                            <a href="{{ route('orders.edit', $o->order_id) }}" class="btn btn-sm btn-primary text-white me-2" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-light text-white btnDelete" title="Delete" data-id="{{ $o->order_id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                            <button
                                              class="btn btn-sm btn-warning"
                                              data-bs-toggle="modal"
                                              data-bs-target="#paymentModal"
                                              data-order-id="{{ $o->order_id }}"
                                              data-unpaid="{{ $snap['unpaid'] }}"
                                              title="Add Payment">
                                              <i class="fa fa-inr"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="14" class="text-center">No orders found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="card">
    <div class="card-body">

    <div id="pm_history">
    <div class="text-center py-4" id="pm_history_loader" style="display:none;">
      Loading history...
    </div>
  </div>

  <hr class="my-3">

  {{-- Add Payment form --}}
    <form method="POST" action="{{ route('payments.store') }}" class="modal-content">
      @csrf
        <input type="hidden" name="order_id" id="pm_order_id">

      <div class="modal-header">
        <h5 class="modal-title">Add Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">

        <div class="mb-2">
          <label class="form-label">Due Amount</label>
          <input type="text" id="pm_unpaid" class="form-control" readonly>
        </div>

        <div class="mb-2">
          <label class="form-label">Paid Amount <span class="text-danger">*</span></label>
          <input type="number" min="1" step="1" name="paid_amount" class="form-control" required>
          <small class="text-muted">Cannot exceed current unpaid.</small>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Save Payment</button>
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>

</div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.toggle-receive-form').forEach(function(form){
  form.addEventListener('submit', function(e){
    e.preventDefault();
    const isNotReceived = this.dataset.current === '1';
    const orderId = this.dataset.orderId;
    const nextState = isNotReceived ? 'Received' : 'Not Received';
    const msg = `Are you sure you want to mark Order #${orderId} as ${nextState}?`;
    if (confirm(msg)) this.submit();
  });
});
$(function(){
    const CSRF='{{ csrf_token() }}';

    // check all
    $('#checkAll').on('change', function(){ $('.row-check').prop('checked', $(this).is(':checked')); });

    // bulk delete (soft)
    $('#btnBulkDelete').on('click', function(){
        let ids = $('.row-check:checked').map(function(){ return $(this).val(); }).get();
        if(!ids.length) return alert('Please select at least one row.');
        if(!confirm('Are you sure you want to delete selected records?')) return;

        $.ajax({
            url: "{{ route('orders.bulk-delete') }}",
            type: 'POST',
            data: { ids: ids, _token: CSRF },
            success: function(r){ if(r.status) location.reload(); else alert(r.message || 'Failed to delete.'); },
            error: function(){ alert('Something went wrong.'); }
        });
    });

    // single delete (soft)
    $('.btnDelete').on('click', function(){
        let id = $(this).data('id');
        if(!confirm('Do you really want to delete this record?')) return;

        $.ajax({
            url: "{{ route('orders.destroy', ':id') }}".replace(':id', id),
            type: 'POST',
            data: { _method: 'DELETE', _token: CSRF },
            success: function(r){ if(r.status) location.reload(); else alert('Failed to delete.'); },
            error: function(){ alert('Something went wrong.'); }
        });
    });

    // toggle status
    $('.toggle-status').on('click', function(){
        let id = $(this).data('id'), el=$(this);
        $.ajax({
            url: "{{ route('orders.change-status', ':id') }}".replace(':id', id),
            type: 'POST',
            data: { _token: CSRF },
            success: function(r){
                if(r.status){
                    if(r.new_status==1){ el.removeClass('bg-secondary').addClass('bg-success').text('Active'); }
                    else { el.removeClass('bg-success').addClass('bg-secondary').text('Inactive'); }
                }
            }
        });
    });
});

// for payment 

document.getElementById('paymentModal').addEventListener('show.bs.modal', function (event) {
  const btn = event.relatedTarget;
  const orderId = btn.getAttribute('data-order-id');
  const unpaid  = btn.getAttribute('data-unpaid');

  document.getElementById('pm_order_id').value = orderId;
  document.getElementById('pm_unpaid').value   = '₹' + Number(unpaid).toLocaleString('en-IN');

  const historyWrap   = document.getElementById('pm_history');
  const historyLoader = document.getElementById('pm_history_loader');

  // show loader
  if (historyLoader) historyLoader.style.display = 'block';
  historyWrap.innerHTML = historyLoader ? historyLoader.outerHTML : 'Loading...';

  // Build URL from named route pattern
  let url = "{{ route('payments.history', ':id') }}";
  url = url.replace(':id', orderId);

  fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.text())
    .then(html => {
      historyWrap.innerHTML = html;
    })
    .catch(() => {
      historyWrap.innerHTML = '<div class="alert alert-danger">Unable to load payment history.</div>';
    });
});

</script>
@endsection
