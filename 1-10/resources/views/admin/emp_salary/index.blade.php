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
                <th>Status</th>
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
                  <td>
                    @if($row->iStatus==1)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <button type="button"
                      class="btn btn-sm btn-primary editBtn"
                      data-id="{{ $row->emp_salary_id }}"
                      data-emp="{{ $row->emp_id }}"
                      data-date="{{ \Carbon\Carbon::parse($row->salary_date)->format('Y-m-d\TH:i') }}"
                      data-amount="{{ $row->salary_amount }}"
                      data-status="{{ $row->iStatus }}"
                      data-bs-toggle="modal" data-bs-target="#salaryModal">
                      <i class="fa fa-edit"></i>
                    </button>

                    <form method="POST" action="{{ route('emp-salaries.destroy', $row->emp_salary_id) }}" class="d-inline">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-danger"
                              onclick="return confirm('Delete this salary record?')">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
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
  <div class="modal-dialog">
    <form method="POST" id="salaryForm" class="modal-content">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">

      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Add Salary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Employee <span class="text-danger">*</span></label>
          <select name="emp_id" id="emp_id" class="form-select" required>
            <option value="">Select Employee</option>
            @foreach($employees as $e)
              <option value="{{ $e->emp_id }}">{{ $e->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Salary Date & Time <span class="text-danger">*</span></label>
          <input type="datetime-local" name="salary_date" id="salary_date" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
          <input type="number" name="salary_amount" id="salary_amount" class="form-control" min="0" step="1" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="iStatus" id="iStatus" class="form-select">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Save</button>
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form        = document.getElementById('salaryForm');
  const methodInput = document.getElementById('formMethod');
  const modalTitle  = document.getElementById('modalTitle');

  // Open modal — decide Add vs Edit
  document.getElementById('salaryModal').addEventListener('show.bs.modal', function (e) {
    const trigger = e.relatedTarget;

    // ADD
    if (!trigger || !trigger.classList.contains('editBtn')) {
      form.action = "{{ route('emp-salaries.store') }}";
      methodInput.value = "POST";
      modalTitle.innerText = "Add Salary";
      form.reset();
      return;
    }

    // EDIT
    const id     = trigger.dataset.id;
    const emp    = trigger.dataset.emp;
    const date   = trigger.dataset.date;   // format: Y-m-d\TH:i for datetime-local
    const amount = trigger.dataset.amount;
    const status = trigger.dataset.status;

    // Route to PUT / update
    form.action = "{{ route('emp-salaries.update', ':id') }}".replace(':id', id);
    methodInput.value = "PUT";
    modalTitle.innerText = "Edit Salary";

    // Fill fields
    document.getElementById('emp_id').value       = emp || '';
    document.getElementById('salary_date').value  = date || '';
    document.getElementById('salary_amount').value= amount || '';
    document.getElementById('iStatus').value      = status || '1';
  });
});
</script>
@endsection
