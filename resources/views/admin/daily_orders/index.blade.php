@extends('layouts.app')
@section('title','Daily Orders')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
          
               {{-- Alert Messages --}}
            @include('common.alert')
        <div class="card"> 
          <div class="card-header">
            <h5> Customer Listing
            </h5>
          </div>
            <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('customer.index') }}" class="d-flex">
                        <input type="text" name="customer_name" class="form-control me-2" placeholder="Customer Name" value="{{ request('customer_name') }}">
                        <input type="text" name="customer_mobile" class="form-control me-2" placeholder="Mobile Number" value="{{ request('customer_mobile') }}">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ route('daily-orders.index') }}" class="btn btn-light">Reset</a>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('daily-orders.create') }}" class="btn btn-sm btn-primary">
                        <i class="far fa-plus"></i> Add New
                    </a>
                </div>
            </div>

        {{-- RIGHT: Listing --}}
        <div class="col-lg-12">
          <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent Orders</h5></div>
            <div class="card-body table-responsive">
              <table class="table table-sm align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Service</th>
                    <th class="text-end">Total (₹)</th>
                    <th class="text-end">Paid (₹)</th>
                    <th class="text-end">Unpaid (₹)</th>
                        <th>Sts</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($rows as $r)
                  @php
                      $paid = (float)($r->paid_sum ?? 0);
                      $due  = max(0, (float)$r->amount - $paid);
                    @endphp
                    <tr>
                      <td>{{ $r->daily_order_id }}</td>
                      <td>{{ \Carbon\Carbon::parse($r->rent_date)->format('d-m-Y') }}</td>
                      <td>
                        {{ $r->customer_name }}
                        @if((int)$r->customer_id === 0)
                          <span class="badge bg-info ms-1">Retail</span>
                        @else
                          <span class="badge bg-secondary ms-1">Recurring</span>
                        @endif
                      </td>
                      <td>{{ $r->mobile }}</td>
                      <td>{{ $r->service_type }}</td>
                      <td class="text-end">₹{{ number_format((float)$r->amount, 2) }}</td>
                      <td class="text-end">₹{{ number_format($paid, 2) }}</td>
                      <td class="text-end {{ $due > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                        ₹{{ number_format($due, 2) }}
                      </td>

                      <td>@if($r->iStatus) <span class="badge bg-success">Active</span> @else <span class="badge bg-secondary">Inactive</span> @endif</td>
                      <td>
                       <a href="{{ route('daily-orders.edit', $r->daily_order_id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                        <form action="{{ route('daily-orders.destroy', $r->daily_order_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this order?');">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                        </form>
                        <button
                            type="button" title="Pay"
                            class="btn btn-sm btn-success btnPay"
                            data-bs-toggle="modal"
                            data-bs-target="#paymentModal"
                            data-order-id="{{ $r->daily_order_id }}"
                            data-customer-id="{{ $r->customer_id }}"
                            data-customer-name="{{ $r->customer_name }}"
                            data-service="{{ $r->service_type }}"
                            data-order-date="{{ \Carbon\Carbon::parse($r->rent_date)->format('Y-m-d') }}"
                          ><i class="fas fa-inr"></i></button>
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="8" class="text-center text-muted">No records</td></tr>
                  @endforelse
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="5" class="text-end fw-semibold">Page Totals:</td>
                    <td class="text-end fw-semibold">₹{{ number_format($page_total_amount, 2) }}</td>
                    <td class="text-end fw-semibold">₹{{ number_format($page_total_paid, 2) }}</td>
                    <td class="text-end fw-semibold {{ $page_total_due > 0 ? 'text-danger' : 'text-success' }}">
                      ₹{{ number_format($page_total_due, 2) }}
                    </td>
                    <td colspan="2"></td>
                  </tr>
                </tfoot>
              </table>

              <div class="mt-2">{{ $rows->links() }}</div>
            </div>
          </div>
        </div>

      </div>{{-- row --}}
    </div>
  </div>
</div>


{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Receive Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="paymentForm" method="POST" action="#">
        @csrf
        <div class="modal-body">
          <div class="mb-2">
            <div class="small text-muted" id="payCustomerInfo">Customer: —</div>
            <div class="small text-muted" id="payOrderInfo">Order: —</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0.01" class="form-control" name="amount" id="payAmount" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="entry_date" id="payDate" value="{{ now()->format('Y-m-d') }}">
          </div>

          <div class="mb-2">
            <label class="form-label">Comment</label>
            <input type="text" class="form-control" name="comment" id="payComment" placeholder="Payment received">
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-success" type="submit">Save Payment</button>
          <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
(function(){
  const paymentModal = document.getElementById('paymentModal');
  const paymentForm  = document.getElementById('paymentForm');
  const payCustomerInfo = document.getElementById('payCustomerInfo');
  const payOrderInfo    = document.getElementById('payOrderInfo');
  const payAmount  = document.getElementById('payAmount');
  const payDate    = document.getElementById('payDate');
  const payComment = document.getElementById('payComment');

  document.querySelectorAll('.btnPay').forEach(btn => {
    btn.addEventListener('click', () => {
      const orderId   = btn.dataset.orderId;
      const custName  = btn.dataset.customerName || '—';
      const custId    = btn.dataset.customerId || '';
      const service   = btn.dataset.service || '';
      const orderDate = btn.dataset.orderDate || '';

      // Set form action to /daily-orders/{id}/payment
      paymentForm.action = "{{ route('daily-orders.payment', ':id') }}".replace(':id', orderId);

      // Prefill UI
      payCustomerInfo.textContent = `Customer: ${custName} (ID: ${custId})`;
      payOrderInfo.textContent    = `Order #${orderId} • ${service} • ${orderDate}`;
      payAmount.value  = '';
      payComment.value = `Payment received for Order #${orderId}`;
      if(!payDate.value) {
        const today = new Date().toISOString().slice(0,10);
        payDate.value = today;
      }
    });
  });
})();
</script>
@endsection
