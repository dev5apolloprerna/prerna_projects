@extends('layouts.app')
@section('title','Employee Salary')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      @include('common.alert')

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Employee Salary List</h5>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#salaryModal">
            <i class="fa fa-plus"></i> Add Salary
          </button>
        </div>

        {{-- Filters --}}
        <div class="card-body">
          <form method="GET" action="{{ route('emp-salaries.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label small text-muted">Employee</label>
              <select name="emp_id" class="form-select">
                <option value="">All Employees</option>
                @foreach($employees as $e)
                  <option value="{{ $e->emp_id }}" {{ (string)$empId === (string)$e->emp_id ? 'selected' : '' }}>
                    {{ $e->name ?? '-' }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label small text-muted">Search</label>
              <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Name / Mobile / Email">
            </div>

            <div class="col-md-2">
              <label class="form-label small text-muted">From</label>
              <input type="date" name="from" value="{{ $from }}" class="form-control">
            </div>

            <div class="col-md-2">
              <label class="form-label small text-muted">To</label>
              <input type="date" name="to" value="{{ $to }}" class="form-control">
            </div>

            <div class="col-md-2">
              <label class="form-label small text-muted">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <option value="1" {{ $status==='1'?'selected':'' }}>Active</option>
                <option value="0" {{ $status==='0'?'selected':'' }}>Inactive</option>
              </select>
            </div>

            <div class="col-12 d-flex gap-2">
              <button class="btn btn-primary">Search</button>
              <a href="{{ route('emp-salaries.index') }}" class="btn btn-light">Reset</a>
            </div>
          </form>
        </div>

        <div class="px-3 pb-2">
          <span class="badge bg-light text-dark border">Total Amount (filtered): <strong>₹{{ number_format($totals) }}</strong></span>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead>
              <tr class="bg-danger text-white">
                <th>Sr No</th>
                <th>Employee</th>
                <th>Salary Date</th>
                <th class="text-end">Amount (₹)</th>
                <!--<th>Status</th>-->
                <th style="width:120px;">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $row)
                <tr>
                  <td>{{ ($rows->currentPage()-1)*$rows->perPage() + $loop->iteration }}</td>
                  <td>{{ $row->employee->name ?? '—' }}</td>
                  <td>{{ \Carbon\Carbon::parse($row->salary_date)->format('d-M-Y') }}</td>
                  <td class="text-end">₹{{ number_format($row->salary_amount) }}</td>
                  <!--<td>
                    @if($row->iStatus==1)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-secondary">Inactive</span>
                    @endif
                  </td>-->
                  <td>
                    <button type="button"
                            class="btn btn-sm btn-warning editBtn"
                            data-bs-toggle="modal"
                            data-bs-target="#salaryModal"
                            {{-- data payload for JS --}}
                            data-id="{{ $row->emp_salary_id }}"
                            data-emp="{{ $row->emp_id }}"
                            data-salary_date="{{ \Carbon\Carbon::parse($row->salary_date)->toDateString() }}"  {{-- From --}}
                            data-last_date="{{ \Carbon\Carbon::parse($row->last_date ?? $row->salary_date)->toDateString() }}" {{-- To --}}
                            data-amount="{{ (int)$row->salary_amount }}"
                            data-status="{{ (int)$row->iStatus }}"
                    >
                          <i class="fa fa-edit"></i>
                        </button>


                    <form method="POST" action="{{ route('emp-salaries.destroy', $row->emp_salary_id) }}" class="d-inline">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-danger"
                              onclick="return confirm('Delete this salary record?')">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                    
                        <a href="{{ route('attendance.employee', [
                            'emp'  => $row->emp_id,
                            'from' => now()->startOfMonth()->toDateString(),
                            'to'   => now()->endOfMonth()->toDateString(),
                          ]) }}"
                       class="btn btn-sm btn-primary"
                       title="View Attendance">
                      <i class="fa fa-eye"></i>
                    </a>
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center">No records found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="px-3 pb-3">
          {{ $rows->links() }}
        </div>
      </div>

    </div>
  </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="salaryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="{{ route('emp-salaries.store') }}" id="salaryForm" class="modal-content">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">

      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Add Salary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        {{-- Employee --}}
        <div class="mb-3">
          <label class="form-label">Employee <span class="text-danger">*</span></label>
          <select name="emp_id" id="emp_id" class="form-select" required>
            <option value="">Select Employee</option>
            @foreach($employees as $e)
              <option value="{{ $e->emp_id }}">{{ $e->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">From (Salary Date) <span class="text-danger">*</span></label>
            <input type="date" name="salary_date" id="salary_date" class="form-control"
                   value="{{ old('salary_date') }}" placeholder="Auto…" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">To (Last Date) <span class="text-danger">*</span></label>
            <input type="date" name="last_date" id="last_date" class="form-control"
                   value="{{ old('last_date', \Carbon\Carbon::now()->toDateString()) }}" required>
          </div>
        </div>

        <div class="mt-3 mb-1">
          <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
          <input type="number" min="0" name="salary_amount" id="salary_amount"
                 class="form-control" value="{{ old('salary_amount') }}" placeholder="Auto-calculated…" required>
        </div>
        <div class="large text-primary" id="amount_hint"></div>

      <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label"> Mobile Recharge </label>
            <input type="text" name="mobile_recharge" id="mobile_recharge" class="form-control"
                   value="{{ old('mobile_recharge') }}" placeholder="Amount">
          </div>
          <div class="col-md-6">
          <label class="form-label">Withdrawal Deduction (₹)</label>
          <input type="number" min="0" name="withdrawal_deducted"  id="withdrawal_amount" class="form-control" value="{{ old('withdrawal_amount') }}">
          <small class="text-muted">Auto-deducted from active employee withdrawals.</small>
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label">Status</label>
          <select name="iStatus" id="iStatus" class="form-select">
            <option value="1" selected>Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>


@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal      = document.getElementById('salaryModal');
  const form       = document.getElementById('salaryForm');
  const methodIn   = document.getElementById('formMethod');
  const modalTitle = document.getElementById('modalTitle');

  const emp    = document.getElementById('emp_id');
  const fromEl = document.getElementById('salary_date'); // FROM
  const toEl   = document.getElementById('last_date');   // TO
  const amtEl  = document.getElementById('salary_amount');
  const deduct  = document.getElementById('withdrawal_amount');
  const hint   = document.getElementById('amount_hint');
  const status = document.getElementById('iStatus');

  let isEdit   = false;
  let fromAuto = true; // if user types From manually, we stop auto-overwriting

  fromEl.addEventListener('input', () => { fromAuto = false; });

  async function fetchLastRange(empId) {
    const url = `{{ route('emp-salaries.last-range') }}?emp_id=${encodeURIComponent(empId)}`;
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) return null;
    return res.json();
  }

  async function quote() {
    const emp_id = emp.value;
    const salary_date = fromEl.value; // FROM (date)
    const last_date   = toEl.value;   // TO (date)
    if (!emp_id || !salary_date || !last_date) return;

    const qs  = new URLSearchParams({ emp_id, salary_date, last_date }).toString();
    const res = await fetch(`{{ route('emp-salaries.quote-attendance') }}?${qs}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!res.ok) return;
    const data = await res.json();
    if (data.ok) {
      amtEl.value = data.amount;
      deduct.value = data.emi_amount;
      hint.textContent = `${data.note} (P=${data.counts.P ?? 0}, H=${data.counts.H ?? 0}, A=${data.counts.A ?? 0})`;
    } else {
      hint.textContent = '';
    }
  }

  // Open modal — decide Add vs Edit
  modal.addEventListener('show.bs.modal', async function (e) {
    const trigger = e.relatedTarget;
    fromAuto = true;

    // ===== ADD =====
    if (!trigger || !trigger.classList.contains('editBtn')) {
      isEdit = false;
      form.action = "{{ route('emp-salaries.store') }}";
      methodIn.value = "POST";
      modalTitle.innerText = "Add Salary";
      form.reset();
      hint.textContent = '';

      // If an employee is already selected in the filter, keep it; else wait for user to pick
      // When an employee is picked we will pull last-range and quote.

      return;
    }

    // ===== EDIT =====
    isEdit = true;
    const id     = trigger.dataset.id;
    const emp_id = trigger.dataset.emp;
    const from   = trigger.dataset.salary_date; // 'YYYY-MM-DD'
    const to     = trigger.dataset.last_date;   // 'YYYY-MM-DD'
    const amount = trigger.dataset.amount;
    const istat  = trigger.dataset.status;

    form.action     = "{{ route('emp-salaries.update', ':id') }}".replace(':id', id);
    methodIn.value  = "PUT";
    modalTitle.innerText = "Edit Salary";

    emp.value    = emp_id || '';
    fromEl.value = from || '';
    toEl.value   = to   || '';
    amtEl.value  = amount || '';
    status.value = istat ?? '1';

    // For edit we don't auto-change dates; but we can re-quote to refresh the hint
    hint.textContent = '';
    quote();
  });

  // When employee changes in ADD mode → default dates from last payment
  emp.addEventListener('change', async () => {
    if (!emp.value) return;

    if (!isEdit) {
      const data = await fetchLastRange(emp.value);
      if (data && data.ok) {
        // From = last_date + 1 day (or today if none)
        if (fromAuto || !fromEl.value) fromEl.value = data.from_default;
        // To = today by default
        if (!toEl.value) toEl.value = data.to_default;
        // If To < From, push To = From
        if (toEl.value < fromEl.value) toEl.value = fromEl.value;
      }
    }

    quote();
  });

  // Quote whenever dates change
  fromEl.addEventListener('change', () => {
    // Ensure TO is not earlier than FROM
    if (toEl.value && toEl.value < fromEl.value) toEl.value = fromEl.value;
    quote();
  });

  toEl.addEventListener('change', () => {
    if (fromEl.value && toEl.value < fromEl.value) toEl.value = fromEl.value;
    quote();
  });
});
</script>

@endsection
