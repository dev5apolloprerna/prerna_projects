<div class="modal fade" id="editTankerModal" tabindex="-1" aria-labelledby="editTankerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="editTankerForm" action="">
      @csrf
      <!-- @method('PUT') -->

      {{-- Keep the id so we can rebuild action after validation fails --}}
      <input type="hidden" name="tanker_id" id="edit_tanker_id" value="{{ old('tanker_id') }}">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Tanker</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tanker Name <span style="color:red;">*</span></label>
            <input type="text" name="tanker_name" class="form-control @error('tanker_name') is-invalid @enderror"
                   value="{{ old('tanker_name') }}">
            @error('tanker_name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Tanker Code <span style="color:red;">*</span></label>
            <input type="text" name="tanker_code" class="form-control @error('tanker_code') is-invalid @enderror"
                   value="{{ old('tanker_code') }}">
            @error('tanker_code')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Status <span style="color:red;">*</span></label>
            <select name="status" class="form-control @error('status') is-invalid @enderror">
              <option value="0" {{ old('status')==='0' ? 'selected' : '' }}>Inside</option>
              <option value="1" {{ old('status')==='1' ? 'selected' : '' }}>Outside</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Auto-reopen the modal on validation errors + restore the form action --}}
@if ($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
  const id   = @json(old('tanker_id'));
  const form = document.getElementById('editTankerForm');

  // Set the RESTful update URL using the old id (adjust base path if needed)
  if (id) {
    form.action = "{{ url('admin/tanker') }}/" + id; // e.g. /admin/tanker/{id}
  }

  const modal = new bootstrap.Modal(document.getElementById('editTankerModal'));
  modal.show();
});
</script>
@endif
