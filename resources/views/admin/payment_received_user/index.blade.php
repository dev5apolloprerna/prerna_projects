@extends('layouts.app')

@section('title', 'Payment Received User')

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
              <h5 class="card-title mb-3">Add Payment Received User</h5>

              <form method="POST" action="{{ route('payment-received-user.store') }}">
                @csrf
                <div class="mb-3">
                  <label class="form-label">Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" class="form-control" value="{{ old('name') }}" maxlength="100" placeholder="Enter user name">
                  @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="iStatus" class="form-control">
                    <option value="1" {{ old('iStatus', 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('iStatus') === '0' ? 'selected' : '' }}>Inactive</option>
                  </select>
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
              <h5 class="mb-0">Payment Received User List</h5>

              <form method="GET" action="{{ route('payment-received-user.index') }}" class="d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search name...">
                <button class="btn btn-sm btn-primary">Search</button>
              </form>
            </div>

            <div class="card-body">

              <form id="bulkDeleteForm" method="POST" action="{{ route('payment-received-user.delete') }}">
                @csrf

                <div class="table-responsive">
                  <button type="submit" class="btn btn-danger btn-sm mb-3" onclick="return confirm('Delete selected users?')">
                    Bulk Delete
                  </button>

                  <table class="table table-striped align-middle">
                    <thead>
                      <tr>
                        <th style="width:28px"><input type="checkbox" id="checkAll"></th>
                        <th>Name</th>
                        <th>Status</th>
                        <th style="width:160px">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($users as $row)
                        <tr>
                          <td><input type="checkbox" name="ids[]" value="{{ $row->received_id }}" class="row-check"></td>
                          <td>{{ $row->name }}</td>
                          <td>
                            <span class="badge {{ $row->iStatus ? 'bg-success' : 'bg-secondary' }}">
                              {{ $row->iStatus ? 'Active' : 'Inactive' }}
                            </span>
                          </td>
                          <td>
                            <button type="button"
                              class="btn btn-sm btn-primary btn-edit"
                              data-id="{{ $row->received_id }}"
                              data-name="{{ $row->name }}"
                              data-istatus="{{ $row->iStatus }}">
                              <i class="fa fa-edit"></i>
                            </button>

                            <form method="POST" action="{{ route('payment-received-user.delete') }}" style="display:inline-block" onsubmit="return confirm('Delete this record?')">
                              @csrf
                              <input type="hidden" name="ids[]" value="{{ $row->received_id }}">
                              <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center text-muted">No records found.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                  
                  <div>{{ $users->links() }}</div>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>

      {{-- Edit Modal --}}
      <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form id="editForm">
              @csrf @method('PUT')
              <div class="modal-header">
                <h5 class="modal-title">Edit Payment Received User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" id="edit_id">
                <div id="edit_error" class="text-danger small mb-2"></div>

                <div class="mb-3">
                  <label class="form-label">Name <span class="text-danger">*</span></label>
                  <input type="text" id="edit_name" class="form-control" maxlength="100">
                </div>

                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select id="edit_iStatus" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- Scripts --}}
@section('scripts')
<script>
(function () {
  const modalEl   = document.getElementById('editUserModal');
  const form      = document.getElementById('editForm');
  const idEl      = document.getElementById('edit_id');
  const nameEl    = document.getElementById('edit_name');
  const statusEl  = document.getElementById('edit_iStatus');
  const errorEl   = document.getElementById('edit_error');
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  // Open edit modal
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-edit');
    if (!btn) return;

    idEl.value = btn.dataset.id || '';
    nameEl.value = btn.dataset.name || '';
    statusEl.value = btn.dataset.istatus || '1';
    errorEl.textContent = '';

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  });

  // Update request
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    errorEl.textContent = '';
    nameEl.classList.remove('is-invalid');

    const id = idEl.value;
    const url = `{{ url('admin/payment-received-user/update') }}/${id}`;

    const fd = new FormData();
    fd.append('_token', csrfToken);
    fd.append('name', nameEl.value.trim());
    fd.append('iStatus', statusEl.value);

    try {
      const res = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json' }, body: fd });

      if (res.ok) {
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        location.reload();
        return;
      }

      if (res.status === 422) {
        const data = await res.json();
        const msg = (data.errors && data.errors.name && data.errors.name[0]) || data.message || 'Validation failed.';
        errorEl.textContent = msg;
        nameEl.classList.add('is-invalid');
        return;
      }

      errorEl.textContent = 'Something went wrong.';
    } catch (err) {
      errorEl.textContent = 'Network error. Please try again.';
    }
  });
})();
</script>

<script>
(function () {
  const checkAll = document.getElementById('checkAll');
  if (checkAll) {
    checkAll.addEventListener('change', function (e) {
      document.querySelectorAll('.row-check').forEach(cb => cb.checked = e.target.checked);
    });
  }
})();
</script>
@endsection
@endsection
