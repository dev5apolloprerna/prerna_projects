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

        {{-- FILTERS (only text search now) --}}
        <div class="card-body">
          <form method="GET" action="{{ route('attendance.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="form-label small text-muted">Search By Employee Name</label>
              <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Enter employee name / mobile">
            </div>

            <div class="col-4 d-flex gap-2">
              <button class="btn btn-primary">Search</button>
              <a href="{{ route('attendance.index') }}" class="btn btn-danger">Reset</a>
            </div>
          </form>
        </div>

        {{-- TABLE + DATE + BULK BUTTONS --}}
        <form method="POST" action="{{ route('attendance.store') }}" id="attendanceForm">
          @csrf

          {{-- This hidden field is what controller reads for upsert --}}
          <input type="hidden" name="attendance_date" id="attendance_date_hidden" value="{{ $date }}">

          <div class="px-3 pb-2 d-flex flex-wrap gap-2 align-items-end justify-content-between">
            <div class="d-flex align-items-end gap-2">
              <div>
                <label class="form-label small text-muted mb-1">Attendance Date</label>
                <input type="date" id="attDate" class="form-control" value="{{ $date }}">
              </div>
              <div class="pb-1">
                <span class="badge bg-light text-dark">Current: {{ \Carbon\Carbon::parse($date)->format('d-M-Y') }}</span>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-success" id="bulkPresent">Mark Present</button>
              <button type="button" class="btn btn-sm btn-warning" id="bulkHalf">Half Day</button>
              <button type="button" class="btn btn-sm btn-secondary" id="bulkAbsent">Absent</button>
            </div>
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
                  <th>Attendance ({{ \Carbon\Carbon::parse($date)->format('d-M-Y') }})</th>
                  <th style="min-width:120px">Set Status</th>
                  <th style="min-width:120px">Reason</th>
                  <th>Action</th>
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
                    <td>
                      @php
                        $prevReason = $existingReason[$emp->emp_id] ?? '';
                        $needsReason = in_array($pre, ['A','H']);
                      @endphp
                      <input type="text"
                            class="form-control form-control-sm reasonInput"
                            name="attendance[{{ $emp->emp_id }}][reason]"
                            value="{{ old('attendance.'.$emp->emp_id.'.reason', $prevReason) }}"
                            placeholder="Reason (optional)"
                            {{ $needsReason ? '' : 'disabled' }}>
                    </td>
                    </td>
                    <td>
                      <a href="{{ route('attendance.employee', [
                        'emp'  => $emp->emp_id,
                        'from' => now()->startOfMonth()->toDateString(),
                        'to'   => now()->endOfMonth()->toDateString(),
                      ]) }}" class="btn btn-sm btn-primary" title="View Attendance">
                        <i class="fa fa-eye"></i>
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="9" class="text-center">No employees found.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="px-3 pb-3 text-end">
            <button type="submit" class="btn btn-primary">Save Attendance</button>
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

  const toggleReason = (row) => {
    const sel = row.querySelector('.setSel');
    const reason = row.querySelector('.reasonInput');
    if (!sel || !reason) return;
    const need = sel.value === 'A' || sel.value === 'H';
    reason.disabled = !need;
    if (!need) reason.value = reason.value; // keep value but disable when Present
  };

  // Bulk set helpers for checked rows
  const setForChecked = (val) => {
    document.querySelectorAll('.rowChk:checked').forEach(chk => {
      const row = chk.closest('tr');
      const sel = row.querySelector('.setSel');
      if (sel) {
        sel.value = val;
        toggleReason(row);
      }
    });
  };

  document.getElementById('bulkPresent')?.addEventListener('click', ()=> setForChecked('P'));
  document.getElementById('bulkHalf')?.addEventListener('click',    ()=> setForChecked('H'));
  document.getElementById('bulkAbsent')?.addEventListener('click',  ()=> setForChecked('A'));

  // Per-row change handling
  document.querySelectorAll('.setSel').forEach(sel => {
    toggleReason(sel.closest('tr'));
    sel.addEventListener('change', () => toggleReason(sel.closest('tr')));
  });

const attDate = document.getElementById('attDate');
  const hidden  = document.getElementById('attendance_date_hidden');
  const form    = document.getElementById('attendanceForm');

  // When date changes -> reload page with ?date=...
  attDate?.addEventListener('change', () => {
    const v = attDate.value;
    if (!v) return;
    const url = new URL(window.location.href);
    url.searchParams.set('date', v);      // keep other params (like q)
    window.location.href = url.toString();
  });

  // Ensure POST carries the same date
  form?.addEventListener('submit', (e) => {
    if (attDate && hidden) {
      hidden.value = attDate.value || hidden.value;
    }
    if (!hidden.value) {
      e.preventDefault();
      alert('Please select a date.');
    }
  });
})();

  
</script>
@endsection
