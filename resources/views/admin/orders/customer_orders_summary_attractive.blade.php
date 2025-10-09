@php
  $fmt = function($v){ return function_exists('money') ? money($v) : '₹'.number_format((float)$v,2); };
@endphp

<div class="customer-summary">

  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-2">
    <div>
      <h5 class="mb-0">
        {{ $customer->customer_name ?? ('Customer #'.$customer->customer_id) }}
      </h5>
      <div class="small text-muted">
        Orders: {{ $totals['orders_count'] }}
        • Daily: {{ $meta['daily_count'] }} • Monthly: {{ $meta['monthly_count'] }}
      </div>
    </div>
    <div>
      <span class="badge bg-success">Received: {{ $meta['received_count'] }}</span>
      <span class="badge bg-danger ms-1">Not Received: {{ $meta['not_received_count'] }}</span>
    </div>
  </div>

  {{-- KPI tiles --}}
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body py-2">
          <div class="text-muted small">Total Due</div>
          <div class="h5 mb-0">{{ $fmt($totals['total_due']) }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body py-2">
          <div class="text-muted small">Paid</div>
          <div class="h5 mb-0">{{ $fmt($totals['paid']) }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm">
        <div class="card-body py-2">
          <div class="text-muted small">Unpaid</div>
          <div class="h5 mb-0 {{ $totals['unpaid']>0 ? 'text-danger' : '' }}">{{ $fmt($totals['unpaid']) }}</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 d-flex align-items-center justify-content-end">
      <div>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="co_expandAll">Expand all</button>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-1" id="co_collapseAll">Collapse all</button>
      </div>
    </div>
  </div>

  {{-- Orders accordion --}}
  @if($orders->isEmpty())
    <div class="alert alert-info mb-0">No orders found for this customer.</div>
  @else
    <div class="accordion" id="custOrdersAcc">
      @foreach($orders as $o)
        @php
          $s = $o->snap ?? [];
          $ordId = $o->order_id;
          $accId = 'co_'.$ordId;
          $status = ((int)$o->isReceive === 1) ? ['Not Received','danger'] : ['Received','success'];
          $basis = $s['rent_basis'] ?? '-';
          $basisTxt = $basis === 'daily'
              ? ($s['days_used'] ?? 0).' day'.((int)($s['days_used'] ?? 0) > 1 ? 's' : '')
              : ($s['months'] ?? 0).' month'.((int)($s['months'] ?? 0) > 1 ? 's' : '');
          $plist = ($payments->get($o->order_id) ?? collect());
        @endphp

        <div class="accordion-item mb-2">
          <h2 class="accordion-header" id="h{{ $accId }}">
            <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#c{{ $accId }}"
                    aria-expanded="false" aria-controls="c{{ $accId }}">
              <div class="w-100">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <strong>#{{ $ordId }}</strong> · {{ ucfirst($o->order_type) }}
                    <span class="badge bg-{{ $status[1] }} ms-2">{{ $status[0] }}</span>
                    <div class="small text-muted">
                      Start: {{ \Carbon\Carbon::parse($o->rent_start_date)->format('d-m-Y') }}
                      • Basis: {{ ucfirst($basis) }} ({{ $basisTxt }})
                      • {{ $o->tanker->tanker_code ?? '-' }}{{ $o->tanker?->tanker_name ? ' · '.$o->tanker->tanker_name : '' }}
                      • {{ $o->tanker_location }}
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="fw-semibold">{{ $fmt($s['total_due'] ?? 0) }}</div>
                    <div class="small">
                      Paid: {{ $fmt($s['paid_sum'] ?? 0) }} ·
                      <span class="{{ ($s['unpaid'] ?? 0) > 0 ? 'text-danger fw-bold' : '' }}">
                        Unpaid: {{ $fmt($s['unpaid'] ?? 0) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </button>
          </h2>

          <div id="c{{ $accId }}" class="accordion-collapse collapse" aria-labelledby="h{{ $accId }}" data-bs-parent="#custOrdersAcc">
            <div class="accordion-body">

              <div class="row g-3">
                {{-- Rent breakdown --}}
                <div class="col-md-5">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead>
                        <tr class="table-light"><th colspan="2">Rent Breakdown</th></tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Base</td>
                          <td class="text-end">{{ $fmt($s['base'] ?? 0) }}</td>
                        </tr>
                        <tr>
                          <td>Extra</td>
                          <td class="text-end">
                            {{ $fmt($s['extra'] ?? 0) }}
                            @if(($s['extra'] ?? 0) > 0 && ($s['extra_days'] ?? 0))
                              <small class="text-muted">(+{{ $s['extra_days'] }} d)</small>
                            @endif
                          </td>
                        </tr>
                        <tr class="table-light">
                          <th>Total Due</th>
                          <th class="text-end">{{ $fmt($s['total_due'] ?? 0) }}</th>
                        </tr>
                        <tr>
                          <td>Paid</td>
                          <td class="text-end">{{ $fmt($s['paid_sum'] ?? 0) }}</td>
                        </tr>
                        <tr>
                          <td>Unpaid</td>
                          <td class="text-end {{ ($s['unpaid'] ?? 0) > 0 ? 'text-danger fw-bold' : '' }}">
                            {{ $fmt($s['unpaid'] ?? 0) }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                {{-- Payments --}}
                <div class="col-md-7">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead>
                        <tr class="table-light">
                          <th style="width:60px;">ID</th>
                          <th>Date</th>
                          <th class="text-end">Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($plist as $pm)
                          <tr>
                            <td>#{{ $pm->payment_id }}</td>
                            <td>{{ \Carbon\Carbon::parse($pm->created_at)->format('d-m-Y H:i') }}</td>
                            <td class="text-end">{{ $fmt($pm->paid_amount) }}</td>
                          </tr>
                        @empty
                          <tr><td colspan="3" class="text-muted">No payments recorded for this order.</td></tr>
                        @endforelse
                      </tbody>
                      <tfoot>
                        <tr class="table-light">
                          <th colspan="2" class="text-end">Paid Total</th>
                          <th class="text-end">{{ $fmt($s['paid_sum'] ?? 0) }}</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

            </div> {{-- /accordion-body --}}
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>

{{-- Expand/Collapse all (scoped to this partial) --}}
<script>
document.addEventListener('DOMContentLoaded', function(){
  const acc = document.getElementById('custOrdersAcc');
  const ex  = document.getElementById('co_expandAll');
  const cl  = document.getElementById('co_collapseAll');
  if(!acc) return;
  if (ex) ex.addEventListener('click', () => {
    acc.querySelectorAll('.accordion-collapse').forEach(el => new bootstrap.Collapse(el, { show: true }));
  });
  if (cl) cl.addEventListener('click', () => {
    acc.querySelectorAll('.accordion-collapse.show').forEach(el => new bootstrap.Collapse(el, { toggle: true }));
  });
});
</script>
