@extends('layouts.app')
@section('title','Employee Attendance')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      @include('common.alert')

      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Employee Attendance List</h5>
        </div>

        {{-- FILTERS (Status select placed beside Date) --}}
        <div class="card-body">
          <form method="GET" action="{{ route('attendance.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label small text-muted">Search By Employee Name</label>
              <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Enter employee name / mobile ">
            </div>

            <div class="col-md-2">
              <label class="form-label small text-muted">Date</label>
              <input type="date" name="date" value="{{ $date }}" class="form-control" placeholder="dd-mm-yyyy">
            </div>

            <div class="col-md-2">
              <label class="form-label small text-muted">Status</label>
              <select name="filter_status" class="form-select">
                <option value="">All</option>
                <option value="P" {{ $filterStatus==='P' ? 'selected':'' }}>Present</option>
                <option value="H" {{ $filterStatus==='H' ? 'selected':'' }}>Half Day</option>
                <option value="A" {{ $filterStatus==='A' ? 'selected':'' }}>Absent</option>
              </select>
            </div>

            <div class="col-12 d-flex gap-2">
              <button class="btn btn-primary">Search</button>
              <a href="{{ route('attendance.index') }}" class="btn btn-danger">Reset</a>
            </div>
          </form>
        </div>

        {{-- TABLE + BULK BUTTONS --}}
        <form method="POST" action="{{ route('attendance.store') }}">
          @csrf
          <input type="hidden" name="attendance_date" value="{{ $date }}">

          <div class="px-3 pb-2 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-sm btn-danger" id="bulkPresent">Mark as Attended</button>
            <button type="button" class="btn btn-sm btn-warning" id="bulkHalf">Mark as Half Day</button>
            <button type="button" class="btn btn-sm btn-secondary" id="bulkAbsent">Mark as Absent</button>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead>
                <tr class="bg-danger text-white">
                  <th style="width:36px" class="text-center">
                    <input type="checkbox" id="masterChk" class="form-check-input">
                  </th>
                  <th>Sr No</th>
                  <th>Employee Name</th>
                  <th>Mobile</th>
                  <th>Current Status</th>
                  <th style="min-width:220px">Set Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($employees as $emp)
                  @php
                    $pre = strtoupper($existing[$emp->emp_id] ?? '-'); // '-', P, H, A
                  @endphp
                  <tr>
                    <td class="text-center">
                      <input class="form-check-input rowChk" type="checkbox" name="selected[]" value="{{ $emp->emp_id }}">
                    </td>
                    <td>{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $emp->name }}</td>
                    <td>{{ $emp->mobile }}</td>
                    <td>
                      @if($pre==='P')
                        <span class="badge bg-success">Present</span>
                      @elseif($pre==='H')
                        <span class="badge bg-warning text-dark">Half Day</span>
                      @elseif($pre==='A')
                        <span class="badge bg-danger">Absent</span>
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      <select class="form-select form-select-sm setSel"
                              name="attendance[{{ $emp->emp_id }}][status]">
                        <option value="P" {{ $pre==='P'?'selected':'' }}>Present</option>
                        <option value="H" {{ $pre==='H'?'selected':'' }}>Half Day</option>
                        <option value="A" {{ $pre==='A'?'selected':'' }}>Absent</option>
                      </select>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="9" class="text-center">No employees found.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="px-3 pb-3 text-end">
            <button type="submit" class="btn btn-primary">
               Save Attendance
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function(){
  const master = document.getElementById('masterChk');
  master?.addEventListener('change', ()=> {
    document.querySelectorAll('.rowChk').forEach(c => c.checked = master.checked);
  });

  // Bulk set helpers for checked rows
  const setForChecked = (val) => {
    document.querySelectorAll('.rowChk:checked').forEach(chk => {
      const row = chk.closest('tr');
      const sel = row.querySelector('.setSel');
      if (sel) sel.value = val;
    });
  };

  document.getElementById('bulkPresent')?.addEventListener('click', ()=> setForChecked('P'));
  document.getElementById('bulkHalf')?.addEventListener('click',    ()=> setForChecked('H'));
  document.getElementById('bulkAbsent')?.addEventListener('click',  ()=> setForChecked('A'));
})();
</script>
@endsection
