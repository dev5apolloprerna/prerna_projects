<div class="mb-3">
  <div class="alert alert-light border">
    <div class="d-flex justify-content-between flex-wrap">
      <div>
        <div><strong>Order #:</strong> {{ $order->order_id }}</div>
        <div><strong>Rent Type:</strong> {{ $order->rent_type }}</div>
        <div><strong>Start:</strong> {{ \Carbon\Carbon::parse($order->rent_start_date)->format('d-M-Y') }}</div>
        <div><strong>Tanker Status:</strong> {!! $order->isReceive ? '<span class="badge bg-warning">Not Received</span>' : '<span class="badge bg-success">Received</span>' !!}
        </div>
      </div>
      <div class="text-end">
        <div><strong>Rent:</strong> ₹{{ number_format($snap['base']) }}</div>
        <div>
          <strong>M/D:</strong>
            @if($snap['rent_basis'] === 'daily')
              ({{ $snap['days_used'] }} day{{ $snap['days_used'] > 1 ? 's' : '' }})
            @else
              ({{ $snap['months'] }} month{{ $snap['months'] > 1 ? 's' : '' }})
            @endif
            </div>

        <div><strong>Total Due:</strong> ₹{{ number_format($snap['total_due']) }}</div>
        <div><strong>Paid:</strong> ₹{{ number_format($snap['paid_sum']) }}</div>
        <div class="{{ $snap['unpaid']>0 ? 'text-danger fw-bold' : '' }}">
          <strong>Unpaid (Live):</strong> ₹{{ number_format($snap['unpaid']) }}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="table-responsive card">
  <table class="table table-sm table-bordered align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>When</th>
        <th>Total (Snapshot)</th>
        <th>Paid</th>
        <th>Unpaid (After Row)</th>
      </tr>
    </thead>
    <tbody>
      @forelse($payments as $p)
        <tr>
          <td>{{ $p->payment_id }}</td>
          <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d-M-Y H:i') }}</td>
          <td>₹{{ number_format((int)$p->total_amount) }}</td>
          <td class="text-success">₹{{ number_format((int)$p->paid_amount) }}</td>
          <td class="{{ (int)$p->unpaid_amount>0 ? 'text-danger' : 'text-success' }}">
            ₹{{ number_format((int)$p->unpaid_amount) }}
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center">No payments yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>