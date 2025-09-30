@extends('layouts.app')

@section('title', 'Expense Types')

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
              <h5 class="card-title mb-3">Add Expense Type</h5>

              <form method="POST" action="{{ route('admin.daily-expence-types.store') }}">
                @csrf
                <div class="mb-3">
                  <label class="form-label">Type <span style="color:red">*</span></label>
                  <input type="text" name="type" class="form-control" value="{{ old('type') }}" maxlength="100" placeholder="e.g., Fuel, Salary">
                  @error('type')<span class="text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="iStatus" class="form-control">
                    <option value="1" {{ old('iStatus',1)==1?'selected':'' }}>Active</option>
                    <option value="0" {{ old('iStatus')==='0'?'selected':'' }}>Inactive</option>
                  </select>
                </div>

                <button type="submit" class="btn btn-success">Submit</button>
                                <button type="reset" class="btn btn-light">Clear</button>
              </form>
            </div>
          </div>
        </div>

        {{-- Right: Listing + Search + Bulk Delete --}}
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Type List</h5>
              <form method="GET" action="{{ route('admin.daily-expence-types.index') }}" class="d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search type name">
                <button class="btn btn-sm btn-primary">Search</button>
              </form>
            </div>

            <div class="card-body">
              <form id="bulkDeleteForm" method="POST" action="{{ route('admin.daily-expence-types.bulk-delete') }}">
                @csrf

                <div class="table-responsive">
                  <table class="table table-striped align-middle">
                    <thead>
                      <tr>
                        <th style="width:28px"><input type="checkbox" id="checkAll"></th>
                        <!-- <th>ID</th> -->
                        <th>Type</th>
                        <th>Status</th>
                        <th style="width:160px">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($types as $row)
                        <tr>
                          <td><input type="checkbox" name="ids[]" value="{{ $row->expence_type_id }}" class="row-check"></td>
                          <!-- <td>{{ $row->expence_type_id }}</td> -->
                          <td>{{ $row->type }}</td>
                          <td>
                            <span class="badge {{ $row->iStatus ? 'bg-success' : 'bg-secondary' }}">
                              {{ $row->iStatus ? 'Active' : 'Inactive' }}
                            </span>
                          </td>
                          <td>
                           <button type="button"
                                class="btn btn-sm btn-primary btn-edit"
                                data-id="{{ $row->expence_type_id }}"
                                data-type="{{ $row->type }}"
                                data-istatus="{{ $row->iStatus }}">
                          <i class="fa fa-edit"></i>
                        </button>

                            <form method="POST" action="{{ route('admin.daily-expence-types.destroy', $row->expence_type_id) }}"
                                  style="display:inline-block" onsubmit="return confirm('Delete this type?')">
                              @csrf @method('DELETE')
                              <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="5" class="text-center text-muted">No records found.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                  <button type="submit" class="btn btn-danger" onclick="return confirm('Delete selected types?')">Bulk Delete</button>
                  <div>{{ $types->links() }}</div>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>

      {{-- Edit Modal --}}
      <div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form id="editForm">
              @csrf @method('PUT')
              <div class="modal-header">
                <h5 class="modal-title">Edit Expense Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" id="edit_id">
 <div id="edit_error" class="text-danger small"></div>
                <div class="mb-3">
                  <label class="form-label">Type <span style="color:red">*</span></label>
                  <input type="text" id="edit_type" class="form-control" maxlength="100">
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

@section('scripts')
<script>
(function () {
  const modalEl   = document.getElementById('editTypeModal');
  const form      = document.getElementById('editForm');
  const idEl      = document.getElementById('edit_id');
  const typeEl    = document.getElementById('edit_type');
  const statusEl  = document.getElementById('edit_iStatus');
  const errorEl   = document.getElementById('edit_error');
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  // Open + prefill
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-edit');
    if (!btn) return;

    idEl.value     = btn.dataset.id || '';
    typeEl.value   = btn.dataset.type || '';
    statusEl.value = btn.dataset.istatus || '1';
    errorEl.textContent = '';

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  });

  // Submit
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    errorEl.textContent = '';
    typeEl.classList.remove('is-invalid');

    const id  = idEl.value;
    const url = `{{ url('admin/daily-expence-types') }}/${id}`;

    const fd = new FormData();
    fd.append('_token', csrfToken);
    fd.append('_method', 'PUT');
    fd.append('type', (typeEl.value || '').trim());
    fd.append('iStatus', statusEl.value || '1');

    try {
      const res = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json' }, body: fd });

      if (res.ok) {
        // success: close and refresh (or update row inline if you want)
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        location.reload();
        return;
      }

      if (res.status === 422) {
        const data = await res.json();
        const msg = (data.errors && data.errors.type && data.errors.type[0]) ||
                    (data.message || 'Validation failed.');
        errorEl.textContent = msg;       // <- show message in the red div
        typeEl.classList.add('is-invalid');
        return;
      }

      errorEl.textContent = 'Something went wrong. Please try again.';
    } catch (err) {
      errorEl.textContent = 'Network error. Please try again.';
    }
  });
})();
</script>

<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Check/Uncheck all
  document.getElementById('checkAll')?.addEventListener('change', function(e){
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = e.target.checked);
  });

  // Edit: open modal and load data


  // Toggle status
  document.querySelectorAll('.btn-toggle').forEach(btn => {
    btn.addEventListener('click', async function(){
      const id = this.dataset.id;
      const res = await fetch(`{{ url('admin/daily-expence-types') }}/${id}/toggle`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrf, 'Accept':'application/json'}
      });
      if (!res.ok) return alert('Toggle failed.');
      location.reload();
    });
  });

})();
</script>
@endsection
@endsection
