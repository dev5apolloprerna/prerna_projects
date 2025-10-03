@extends('layouts.app')
@section('title', 'Employee Attendance')

@section('content')
@php
    // Defaults: current month
    $defaultFrom = now()->startOfMonth()->format('Y-m-d');
    $defaultTo   = now()->endOfMonth()->format('Y-m-d');

    $from = request('from', $defaultFrom);
    $to   = request('to',   $defaultTo);

    // Make counting robust for both Collection and Paginator
    $rows = ($attendances instanceof \Illuminate\Pagination\AbstractPaginator)
            ? $attendances->getCollection()
            : collect($attendances);

    $present = $rows->where('status','P')->count();
    $absent  = $rows->where('status','A')->count();
    $halfDay = $rows->where('status','H')->count();
@endphp

<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      @include('common.alert')

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h4 class="mb-0">
            Attendance – {{ $employee->name ?? $employee->full_name ?? ('Emp #'.$employee->emp_id) }}
          </h4>
          <small class="text-muted">
            <!--Employee ID: {{ $employee->emp_id }}-->
          </small>
        </div>
        <div>
          <a href="{{ url()->previous() }}" class="btn btn-sm btn-primary">Back</a>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          {{-- Filter --}}
          <form method="GET" action="{{ route('attendance.employee', $employee->emp_id) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
              <label class="form-label small text-muted">From</label>
              <input type="date" class="form-control" name="from" value="{{ $from }}">
            </div>
            <div class="col-md-3">
              <label class="form-label small text-muted">To</label>
              <input type="date" class="form-control" name="to" value="{{ $to }}">
            </div>
            <div class="col-md-6 d-flex gap-2">
              <button class="btn btn-primary" type="submit">Search</button>
              <a href="{{ route('attendance.employee', $employee->emp_id) }}" class="btn btn-light">Reset (This Month)</a>
            </div>
          </form>

          {{-- Summary badges (for the shown rows) --}}
          <div class="mt-3 d-flex flex-wrap gap-2">
            <span class="badge bg-success">Present: {{ $present }}</span>
            <span class="badge bg-danger">Absent: {{ $absent }}</span>
            <span class="badge bg-info">Half Day: {{ $halfDay }}</span>
            <span class="badge bg-primary text-white">Total: {{ $rows->count() }}</span>
          </div>

          <div class="table-responsive mt-3">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th style="width: 140px;">Date</th>
                  <th style="width: 120px;">Day</th>
                  <th style="width: 120px;">Status</th>
                  <th>Entered By</th>
                  <th style="width: 180px;">Recorded At</th>
                </tr>
              </thead>
              <tbody>
                @forelse($attendances as $a)
                  @php
                    $dt = \Carbon\Carbon::parse($a->attendance_date);
                    $map = [
                      'P' => ['Present', 'success'],
                      'A' => ['Absent', 'danger'],
                      'L' => ['Leave', 'secondary'],
                      'H' => ['Half Day', 'info'],
                    ];
                    [$label, $badge] = $map[$a->status] ?? ['Unknown','dark'];
                  @endphp
                  <tr>
                    <td>{{ $dt->format('d-m-Y') }}</td>
                    <td>{{ $dt->format('l') }}</td>
                    <td><span class="badge bg-{{ $badge }}">{{ $label }}</span></td>
                    <td>{{ $a->enter_by ?: '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($a->created_at)->format('d M Y, h:i A') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted">No attendance found for the selected range.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination (if provided) --}}
          @if($attendances instanceof \Illuminate\Pagination\AbstractPaginator)
            <div class="d-flex justify-content-center mt-3">
              {{ $attendances->appends(['from' => $from, 'to' => $to])->links() }}
            </div>
          @endif
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
