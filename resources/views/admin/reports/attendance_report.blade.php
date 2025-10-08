@extends('layouts.app')
@section('title', 'Attendance & Payment Report')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      @include('common.alert')

      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
          <h5 class="mb-0">
            <i class="fa fa-calendar-check me-2 text-primary"></i> Attendance Report
          </h5>
          <form method="POST" class="d-flex gap-2 align-items-center">
            @csrf
            <label class="text-muted small">From</label>
            <input type="date" name="from_date" value="{{ $from }}" class="form-control form-control-sm">
            <label class="text-muted small">To</label>
            <input type="date" name="to_date" value="{{ $to }}" class="form-control form-control-sm">
            <button class="btn  btn-primary">Search</button>
            <a href="{{ route('admin.attendance-report.index') }}" class="btn btn-light border">
              Reset
            </a>
          </form>
        </div>

        <div class="card-body">
          <h6 class="text-secondary fw-semibold mb-3">Attendance Summary</h6>
          <div class="table-responsive mb-4">
            <table class="table table-striped align-middle table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Employee</th>
                  <th>Daily Wages (₹)</th>
                  <th>Present Days</th>
                  <th>Absent</th>
                  <th>Payment (₹)</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($summary as $row)
                <tr>
                  <td>{{ $row->employee_name }}</td>
                  <td>{{ number_format($row->daily_wages, 2) }}</td>
                  <td><span class="badge bg-success">{{ $row->present_days }}</span></td>
                  <td><span class="badge bg-danger">{{ $row->absent_days }}</span></td>
                  <td><strong class="text-success">₹{{ number_format($row->payment, 2) }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">No records found.</td></tr>
                @endforelse
              </tbody>
              @if($summary->count())
              <tfoot>
                <tr class="table-success">
                  <th colspan="4" class="text-end">Grand Total</th>
                  <th>₹{{ number_format($grandTotal, 2) }}</th>
                </tr>
              </tfoot>
              @endif
            </table>
          </div>

          <h6 class="text-secondary fw-semibold mb-3">Detailed Attendance</h6>
          <div class="table-responsive">
            <table class="table table-hover align-middle table-bordered">
              <thead class="table-primary">
                <tr>
                  <th>#</th>
                  <th>Date</th>
                  <th>Employee</th>
                  <th>Status</th>
                  <th>Leave Reason</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($records as $r)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ \Carbon\Carbon::parse($r->attendance_date)->format('d-m-Y') }}</td>
                  <td>{{ $r->employee->name ?? '-' }}</td>
                  <td>
                    @if($r->status === 'P')
                      <span class="badge bg-success">Present</span>
                    @elseif($r->status === 'A')
                      <span class="badge bg-danger">Absent</span>
                    @else
                      <span class="badge bg-warning text-dark">Half Day</span>
                    @endif
                  </td>
                  <td>{{ $r->leave_reason ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No records found.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
