@extends('layouts.app')
@section('title', 'Expense Detail')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Expense Details for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h5>
          <a href="{{ route('admin.expence-report.index') }}" class="btn btn-sm btn-light">
            <i class="fa fa-arrow-left"></i> Back
          </a>
        </div>

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Expense Type</th>
                  <th>Amount</th>
                  <th>Comment</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($expences as $exp)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $exp->types->type ?? '-' }}</td>
                    <td>₹{{ number_format($exp->amount, 2) }}</td>
                    <td>{{ $exp->comment ?? '-' }}</td>
                    <td>
                      <span class="badge {{ $exp->iStatus ? 'bg-success' : 'bg-secondary' }}">
                        {{ $exp->iStatus ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="text-center text-muted">No records found.</td></tr>
                @endforelse
              </tbody>
              <tfoot>
                <tr class="table-success">
                  <th colspan="2">Total</th>
                  <th colspan="3">₹{{ number_format($total, 2) }}</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
