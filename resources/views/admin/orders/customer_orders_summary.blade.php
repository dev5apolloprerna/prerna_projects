@php
  // Currency-aware formatter
  $fmt = function($v) {
    return function_exists('money') ? money($v) : '₹' . number_format((float)$v, 2);
  };
@endphp

<div>
  {{-- Header: who + quick totals --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div class="mb-2">
      <h6 class="mb-1">Customer:
        <strong>{{ $customer->customer_name ?? ('#'.$customer->customer_id) }}</strong>
      </h6>
      <div class="small text-muted">{{ $totals['orders_count'] }} order(s)</div>
    </div>

    <div class="text-end mb-2">
      <span class="badge bg-primary me-2" style="font-size: medium;">Total: {{ $fmt($totals['total_due']) }}</span>
      <span class="badge bg-success me-2" style="font-size: medium;">Paid: {{ $fmt($totals['paid']) }}</span>
      <span class="badge bg-danger" style="font-size: medium;">Unpaid: {{ $fmt($totals['unpaid']) }}</span>
    </div>
  </div>

  @if($orders->isEmpty())
    <div class="alert alert-info mb-0">No orders found for this customer.</div>
    @php return; @endphp
  @endif

  {{-- Simple, compact table --}}
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>Order #</th>
          <th>Start</th>
          <th>Basis</th>
          <th class="text-end">Total</th>
          <th class="text-end">Paid</th>
          <th class="text-end">Unpaid</th>
          <th class="text-end">Payments</th>
        </tr>
      </thead>
      <tbody>
        @foreach($orders as $o)
          @php
            $s = $o->snap ?? [];
            $basis = $s['rent_basis'] ?? '-';
            $basisShort = $basis === 'daily'
              ? ((int)($s['days_used'] ?? 0)).'d'
              : ((int)($s['months'] ?? 0)).'m';

            $plist = ($payments->get($o->order_id) ?? collect());
          @endphp

          <tr>
            <td>#{{ $o->order_id }}</td>
            <td>{{ \Carbon\Carbon::parse($o->rent_start_date)->format('d-m-Y') }}</td>
            <td>{{ ucfirst($basis) }} <span class="small text-muted">({{ $basisShort }})</span></td>
            <td class="text-end">{{ $fmt($s['total_due'] ?? 0) }}</td>
            <td class="text-end">{{ $fmt($s['paid_sum'] ?? 0) }}</td>
            <td class="text-end {{ ($s['unpaid'] ?? 0) > 0 ? 'text-danger fw-bold' : '' }}">
              {{ $fmt($s['unpaid'] ?? 0) }}
            </td>
            <td class="text-end">
              @if($plist->isNotEmpty())
                <button
                  class="btn btn-sm btn-outline-secondary"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#pm-{{ $o->order_id }}"
                  aria-expanded="false"
                  aria-controls="pm-{{ $o->order_id }}">
                  View ({{ $plist->count() }})
                </button>
              @else
                <span class="text-muted small">—</span>
              @endif
            </td>
          </tr>

          @if($plist->isNotEmpty())
            <tr class="collapse" id="pm-{{ $o->order_id }}">
              <td colspan="7" class="p-0">
                <ul class="list-group list-group-flush">
                  @foreach($plist as $pm)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <span>{{ \Carbon\Carbon::parse($pm->created_at)->format('d-m-Y H:i') }}</span>
                      <strong>{{ $fmt($pm->paid_amount) }}</strong>
                    </li>
                  @endforeach
                </ul>
              </td>
            </tr>
          @endif
        @endforeach
      </tbody>
    </table>
  </div>
</div>
