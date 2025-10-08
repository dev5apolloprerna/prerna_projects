@extends('layouts.app')
@section('title', 'Expense Report')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      @include('common.alert')

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Daily Expense Report</h5>
          <form method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" value="{{ $start }}" class="form-control form-control-sm" />
            <input type="date" name="end_date" value="{{ $end }}" class="form-control form-control-sm" />
            <button class="btn btn-sm btn-primary">Filter</button>
            <a href="{{ route('admin.expence-report.index') }}" class="btn btn-sm btn-light">Reset</a>
          </form>
        </div>

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Total Expense</th>
                  <th>Entries</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($records as $row)
                  <tr>
                    <td>{{ \Carbon\Carbon::parse($row->expence_date)->format('d M Y') }}</td>
                    <td><strong>₹{{ number_format($row->total_amount, 2) }}</strong></td>
                    <td>{{ $row->count }}</td>
                    <td>
                      <a href="{{ route('admin.expence-report.show', $row->expence_date) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-eye"></i> View Details
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="text-center text-muted">No records found.</td></tr>
                @endforelse
              </tbody>
              @if($records->count())
              <tfoot>
                <tr class="table-success">
                  <th>Grand Total</th>
                  <th colspan="3">₹{{ number_format($grandTotal, 2) }}</th>
                </tr>
              </tfoot>
              @endif
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
