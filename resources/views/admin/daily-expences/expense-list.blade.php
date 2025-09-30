{{-- resources/views/dashboard/expense-list.blade.php --}}
@extends('layouts.app')
@section('title', 'Expenses')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">Expenses ({{ ucfirst($period) }})</h4>
        <div>
          <a href="{{ route('dashboard.expense.list', ['period'=>'today']) }}" class="btn btn-sm btn-outline-secondary">Today</a>
          <a href="{{ route('dashboard.expense.list', ['period'=>'month']) }}" class="btn btn-sm btn-outline-secondary">This Month</a>
        </div>
      </div>

      {{-- Custom range --}}
      <form method="GET" action="{{ route('dashboard.expense.list') }}" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="period" value="custom"/>
        <div class="col-md-3">
          <label class="form-label small text-muted">From</label>
          <input type="date" class="form-control" name="date_from" value="{{ old('date_from', optional($from)->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
          <label class="form-label small text-muted">To</label>
          <input type="date" class="form-control" name="date_to" value="{{ old('date_to', optional($to)->format('Y-m-d')) }}">
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary w-100">Apply</button>
        </div>
      </form>

      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">Total: ₹{{ number_format($totalAmount) }}</div>
            <div class="text-muted small">
              Range: {{ optional($from)->format('d M Y') }} — {{ optional($to)->format('d M Y') }}
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
              <thead>
                <tr>
                  <th style="width: 140px;">Date/Time</th>
                  <th>Type</th>
                  <th>Comment</th>
                  <th class="text-end" style="width: 140px;">Amount (₹)</th>
                  <th style="width: 90px;">Source</th>
                </tr>
              </thead>
              <tbody>
                @forelse($rows as $r)
                  <tr>
                    <td>{{ \Carbon\Carbon::parse($r->txn_date)->format('d M Y, h:i A') }}</td>
                    <td>{{ $r->label }}</td>
                    <td>{{ $r->comment }}</td>
                    <td class="text-end fw-semibold">{{ number_format($r->amount) }}</td>
                    <td>
                      @if($r->src === 'salary')
                        <span class="badge bg-info-subtle text-info">Salary</span>
                      @else
                        <span class="badge bg-secondary-subtle text-secondary">Expense</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="text-center text-muted">No records found.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{ $rows->links() }}
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
