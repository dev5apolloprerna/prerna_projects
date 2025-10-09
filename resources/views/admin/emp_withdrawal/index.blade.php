@extends('layouts.app')
@section('title', 'Employee Extra Withdrawals')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      @include('common.alert')

      <div class="row">
        {{-- Left: Add Form --}}
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-3">Add Employee Withdrawal</h5>
              <form method="POST" action="{{ route('employee-extra-withdrawal.store') }}">
                @csrf
                <div class="mb-3">
                  <label class="form-label">Employee <span class="text-danger">*</span></label>
                  <select name="emp_id" class="form-select" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                      <option value="{{ $emp->emp_id }}">{{ $emp->name }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Withdrawal Date</label>
                  <input type="date" name="withdrawal_date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>

                <div class="mb-3">
                  <label class="form-label">Amount (₹)</label>
                  <input type="number" step="0.01" name="amount" class="form-control" placeholder="Enter amount">
                </div>
                <div>
                  <label class="form-label">EMI Amount</label>
                    <input type="number" name="emi_amount" class="form-control" placeholder="e.g. 2000">
                </div>
                <div class="mb-3">
                  <label class="form-label">Reason</label>
                  <input type="text" name="reason" class="form-control" placeholder="Reason for withdrawal">
                </div>
                
                <!--<div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">EMI Amount</label>
                    <input type="number" name="emi_amount" class="form-control" placeholder="e.g. 2000">
                  </div>
                   <div class="col-md-6 mb-3">
                    <label class="form-label">Remaining Amount</label>
                    <input type="number" name="remaining_amount" class="form-control" placeholder="e.g. 8000">
                  </div> 
                </div>-->

                <div class="mb-3">
                  <label class="form-label">Remarks</label>
                  <textarea name="remarks" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                </div>

                <button type="submit" class="btn btn-success">Submit</button>
                <button type="reset" class="btn btn-light">Clear</button>
              </form>
            </div>
          </div>
        </div>

        {{-- Right: Listing --}}
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Withdrawal List</h5>
              <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search by employee or reason">
                <button class="btn btn-sm btn-primary">Search</button>
              </form>
            </div>

            <div class="card-body">
              <form id="bulkDeleteForm" method="POST" action="{{ route('employee-extra-withdrawal.delete') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm mb-2" onclick="return confirm('Delete selected records?')">Bulk Delete</button>

                <div class="table-responsive">
                  <table class="table table-striped align-middle">
                    <thead>
                      <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($withdrawals as $w)
                      <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $w->withdrawal_id }}" class="row-check"></td>
                        <td>{{ $w->employee->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($w->withdrawal_date)->format('d M Y') }}</td>
                        <td><strong>₹{{ number_format($w->amount, 2) }}</strong></td>
                        <td>{{ $w->reason ?? '-' }}</td>
                        <td>
                          <button type="button" class="btn btn-sm btn-primary editBtn"
                            data-id="{{ $w->withdrawal_id }}"
                            data-emp="{{ $w->emp_id }}"
                            data-date="{{ $w->withdrawal_date }}"
                            data-amount="{{ $w->amount }}"
                            data-reason="{{ $w->reason }}"
                            data-emi="{{ $w->emi_amount }}"
                            data-remaining="{{ $w->remaining_amount }}"
                            data-remarks="{{ $w->remarks }}">
                            <i class="fa fa-edit"></i>
                          </button>
                        </td>
                      </tr>
                      @empty
                        <tr><td colspan="6" class="text-center text-muted">No records found.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="mt-3">{{ $withdrawals->links() }}</div>
              </form>
            </div>
          </div>
        </div>
      </div>

      {{-- Edit Modal --}}
      <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <form id="editForm" method="POST">
              @csrf
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Withdrawal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" id="edit_id">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label>Employee</label>
                    <select id="edit_emp" class="form-select" required>
                      <option value="">Select Employee</option>
                      @foreach($employees as $emp)
                        <option value="{{ $emp->emp_id }}">{{ $emp->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label>Date</label>
                    <input type="date" id="edit_date" class="form-control">
                  </div>
                  <div class="col-md-4">
                    <label>Amount</label>
                    <input type="number" step="0.01" id="edit_amount" class="form-control">
                  </div>
                  <div class="col-md-4">
                    <label>EMI Amount</label>
                    <input type="number" id="edit_emi" class="form-control">
                  </div>
                  <!-- <div class="col-md-4">
                    <label>Remaining</label>
                    <input type="number" id="edit_remaining" class="form-control">
                  </div> -->
                  <div class="col-md-12">
                    <label>Reason</label>
                    <input type="text" id="edit_reason" class="form-control">
                  </div>
                  <div class="col-md-12">
                    <label>Remarks</label>
                    <textarea id="edit_remarks" class="form-control" rows="2"></textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Update</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

@section('scripts')
<script>
document.getElementById('checkAll').addEventListener('change', e => {
  document.querySelectorAll('.row-check').forEach(cb => cb.checked = e.target.checked);
});

document.querySelectorAll('.editBtn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.getElementById('edit_id').value = this.dataset.id;
    document.getElementById('edit_emp').value = this.dataset.emp;
    document.getElementById('edit_date').value = this.dataset.date;
    document.getElementById('edit_amount').value = this.dataset.amount;
    document.getElementById('edit_reason').value = this.dataset.reason;
    document.getElementById('edit_emi').value = this.dataset.emi;
    // document.getElementById('edit_remaining').value = this.dataset.remaining;
    document.getElementById('edit_remarks').value = this.dataset.remarks;
    document.getElementById('editForm').action = `/admin/employee-extra-withdrawal/update/${this.dataset.id}`;
    new bootstrap.Modal(document.getElementById('editModal')).show();
  });
});
</script>
@endsection
@endsection
