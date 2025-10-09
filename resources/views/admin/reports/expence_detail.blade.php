@if($expences->count())
<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle bg-white">
    <thead class="table-primary">
      <tr>
        <th>#</th>
        <th>Expense Type</th>
        <th>Amount</th>
        <th>Comment</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach($expences as $exp)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $exp->types->type ?? '-' }}</td>
        <td><strong class="text-success">₹{{ number_format($exp->amount, 2) }}</strong></td>
        <td>{{ $exp->comment ?? '-' }}</td>
        <td>
          <span class="badge rounded-pill {{ $exp->iStatus ? 'bg-success' : 'bg-secondary' }}">
            {{ $exp->iStatus ? 'Active' : 'Inactive' }}
          </span>
        </td>
      </tr>
      @endforeach
    </tbody>
    <tfoot class="table-light">
      <tr>
        <th colspan="2">Total</th>
        <th colspan="3"><strong>₹{{ number_format($total, 2) }}</strong></th>
      </tr>
    </tfoot>
  </table>
</div>
@else
<div class="text-center text-muted py-3">No records found for this date.</div>
@endif
