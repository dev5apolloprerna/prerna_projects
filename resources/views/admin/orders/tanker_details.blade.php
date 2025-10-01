{{-- Tanker details content (modal body) --}}
<div class="container-fluid">
  <div class="row g-3">

    <div class="col-md-3">
      <div class="text-muted ">Customer</div>
      <div class="fw-semibold">{{ $order->customer->customer_name ?? '-' }}</div>
    </div>

    <div class="col-md-3">
      <div class="text-muted ">Order Type</div>
      <div class="fw-semibold">{{ ucfirst($order->order_type) }}</div>
    </div>

    <div class="col-md-3">
      <div class="text-muted ">Rent Start</div>
      <div class="fw-semibold">{{ \Carbon\Carbon::parse($order->rent_start_date)->format('d-m-Y') }}</div>
    </div>

    <div class="col-md-3">
      <div class="text-muted ">Tanker No</div>
      <div class="fw-semibold">{{ $order->tanker->tanker_code ?? '-' }}</div>
    </div>

    <div class="col-md-5">
      <div class="text-muted ">Tanker Name</div>
      <div class="fw-semibold">{{ $order->tanker->tanker_name ?? '-' }}</div>
    </div>

    <div class="col-md-4">
      <div class="text-muted ">Location</div>
      <div class="fw-semibold">{{ $order->tanker_location ?? '-' }}</div>
    </div>


    <h6 class="card-header mt-4">Refrence Contact Detail</h6>
    <div class="col-md-3">
      <div class="text-muted ">Name</div>
      <div class="fw-semibold">{{ $order->reference_name }}</div>
    </div>

    <div class="col-md-3">
      <div class="text-muted ">Mobile No</div>
      <div class="fw-semibold">{{ $order->tanker->reference_mobile_no ?? '-' }}</div>
    </div>

    <div class="col-md-5">
      <div class="text-muted ">Address</div>
      <div class="fw-semibold">{{ $order->reference_address ?? '-' }}</div>
    </div>

    <h6 class="card-header mt-4">Payment Detail</h6>
    @if($snap)
      <div class="col-md-3">
        <div class="text-muted ">Rent</div>
        <div class="fw-semibold">₹{{ number_format($snap['base']) }}</div>
      </div>

      <div class="col-md-3">
        <div class="text-muted ">Total</div>
        <div class="fw-semibold">₹{{ number_format($snap['total_due']) }}</div>
      </div>

      <div class="col-md-3">
        <div class="text-muted ">Paid</div>
        <div class="fw-semibold">₹{{ number_format($snap['paid_sum']) }}</div>
      </div>

      <div class="col-md-3">
        <div class="text-muted ">Unpaid</div>
        <div class="fw-semibold {{ $snap['unpaid'] > 0 ? 'text-danger' : '' }}">
          ₹{{ number_format($snap['unpaid']) }}
        </div>
      </div>

      <div class="col-12">
        <div class="text-muted ">Extra M/D</div>
        <div class="fw-semibold">
          @if(($snap['rent_basis'] ?? null) === 'daily')
            Daily ({{ $snap['days_used'] }} day{{ $snap['days_used'] > 1 ? 's' : '' }})
          @else
            Monthly ({{ $snap['months'] }} month{{ $snap['months'] > 1 ? 's' : '' }})
          @endif
        </div>
      </div>
    @endif

    <div class="col-12">
      <hr class="my-2">
      <div class="">
        Status:
        @if($order->isReceive == 0)
          <span class="badge bg-success">Received</span>
        @else
          <span class="badge bg-danger">Not Received</span>
        @endif
      </div>
    </div>

  </div>
</div>
