@extends('layouts.app')

@section('title', 'Daily Expenses')

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
              <h5 class="card-title mb-3">Add Expense</h5>

              <form method="POST" action="{{ route('admin.daily-expences.store') }}">
                @csrf

                {{-- Expence Type --}}
                <div class="mb-3">
                  <label class="form-label">Expense Type <span style="color:red">*</span></label>
                    <select name="expence_type_id" class="form-control">
                      <option value="">-- Select Type --</option>
                      @foreach($types as $t)
                        <option value="{{  $t->expence_type_id }}" {{ old('expence_type_id')==$t->expence_type_id ?'selected':'' }}>
                          {{ $t->type }}
                        </option>
                      @endforeach
                    </select>
                  @error('expence_type_id')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                 {{-- Amount --}}
                <div class="mb-3">
                <label class="form-label">Date <span style="color:red">*</span></label>
                <input type="date" 
                       name="expence_date" 
                       class="form-control" 
                       value="{{ old('expence_date', \Carbon\Carbon::now()->toDateString()) }}">
                @error('expence_date')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>


                {{-- Amount --}}
                <div class="mb-3">
                  <label class="form-label">Amount (₹) <span style="color:red">*</span></label>
                  <input type="number" min="0" name="amount" class="form-control" value="{{ old('amount') }}">
                  @error('amount')<span class="text-danger">{{ $message }}</span>@enderror
                </div>

                {{-- Comment --}}
                <div class="mb-3">
                  <label class="form-label">Comment</label>
                  <textarea name="comment" class="form-control" rows="3">{{ old('comment') }}</textarea>
                  @error('comment')<span class="text-danger">{{ $message }}</span>@enderror
                </div>

                {{-- Status --}}
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

           <div class="card-header">
            <h5 class="mb-2">Expense List</h5>

            <form method="GET" action="{{ route('admin.daily-expences.index') }}" class="mb-0">
                {{-- Keep preset when searching again --}}
                @if(!empty($preset))
                  <input type="hidden" name="preset" value="{{ $preset }}">
                @endif

                <div class="row g-2 align-items-end">
                  <div class="col-md-3">
                    <label class="form-label mb-1">From</label>
                    <input type="date" name="from_date" class="form-control form-control-sm"
                           value="{{ old('from_date', $uiFromDate ?? request('from_date')) }}">
                  </div>

                  <div class="col-md-3">
                    <label class="form-label mb-1">To</label>
                    <input type="date" name="to_date" class="form-control form-control-sm"
                           value="{{ old('to_date', $uiToDate ?? request('to_date')) }}">
                  </div>

                  <div class="col-md-3">
                    <label class="form-label mb-1">Expense Type</label>
                    <select name="expence_type_id" class="form-control form-control-sm">
                      <option value="">-- All --</option>
                      @foreach($types as $f)
                        <option value="{{ $f->expence_type_id }}" {{ (string)request('expence_type_id')===(string)$f->expence_type_id ? 'selected' : '' }}>
                          {{ $f->type }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <button class="btn btn-primary">Search</button>
                    <a href="{{ route('admin.daily-expences.index') }}" class="btn btn-light">Reset</a>
                  </div>
                </div>
              </form>


          @isset($filteredTotal)
            <div class="text-end mt-2">
              @if($preset === 'today')
              <span class="badge bg-danger me-2">Filtered: Today</span>
            @elseif($preset === 'month')
              <span class="badge bg-success me-2">Filtered: This Month</span>
            @endif

              <small class="text-muted">Filtered Total: <strong>₹{{ number_format($filteredTotal) }}</strong></small>
            </div>
          @endisset


      </div>

            <div class="card-body">
              <form id="bulkDeleteForm" method="POST" action="{{ route('admin.daily-expences.bulk-delete') }}">
                @csrf

                <div class="table-responsive">
                   <button type="submit" class="btn btn-danger btn-sm mb-3" onclick="return confirm('Delete selected expenses?')">Bulk Delete</button>
                  <table class="table table-striped align-middle">
                    <thead>
                    <tr>
                      <th style="width:28px">
                        <input type="checkbox" id="checkAll">
                      </th>
                      <!--<th>ID</th>-->
                      <th>Type</th>
                      <th>Amount</th>
                      <th>Comment</th>
                      <th>Date</th>
                      <!--<th>Status</th>-->
                      <th style="width:140px">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($expences as $row)
                      <tr>
                        <td>
                          <input type="checkbox" name="ids[]" value="{{ $row->expence_id }}" class="row-check">
                        </td>
                        <!--<td>{{ $row->expence_id }}</td>-->
                        <td>{{ $row->types->type }} </td>
                        <td>₹{{ number_format($row->amount) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($row->comment, 40) ?? '-' }}</td>
                        <td>{{ date('d-m-Y',strtotime($row->expence_date)) ?? '-' }}</td>
                        <!--<td>
                          <span class="badge {{ $row->iStatus ? 'bg-success' : 'bg-secondary' }}">
                            {{ $row->iStatus ? 'Active' : 'Inactive' }}
                          </span>
                        </td>-->
                        <td>
                          <button type="button" class="btn btn-sm btn-warning btn-edit"
                                  data-id="{{ $row->expence_id }}"><i class="fa fa-edit"></i></button>

                          <form method="POST" action="{{ route('admin.daily-expences.destroy', $row->expence_id) }}"
                                style="display:inline-block" onsubmit="return confirm('Delete this expense?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                          </form>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="7" class="text-center text-muted">No expenses found.</td>
                      </tr>
                    @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                 
                  <div>{{ $expences->links() }}</div>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>

      {{-- Edit Modal (Popup) --}}
      <div class="modal fade" id="editExpenceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form id="editForm">
              @csrf @method('PUT')
              <div class="modal-header">
                <h5 class="modal-title">Edit Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">

                <input type="hidden" id="edit_id">

                <div class="mb-3">
                  <label class="form-label">Expense Type <span style="color:red">*</span></label>
                    <select id="edit_expence_type_id" class="form-control">
                      <option value="">-- Select Type --</option>
                      @foreach($types as $t)
                        <option value="{{  $t->expence_type_id }}" {{ old('expence_type_id')==$t->expence_type_id ?'selected':'' }}>
                          {{ $t->type }}
                        </option>
                      @endforeach
                    </select>
                  
                </div>

                <div class="mb-3">
                  <label class="form-label">Amount (₹) <span style="color:red">*</span></label>
                  <input type="number" min="0" id="edit_amount" class="form-control">
                </div>
                
                <div class="mb-3">
                  <label class="form-label">Date (₹) <span style="color:red">*</span></label>
                  <input type="date" id="edit_expence_date" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label">Comment</label>
                  <textarea id="edit_comment" class="form-control" rows="3"></textarea>
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

{{-- JS (jQuery + Bootstrap assumed) --}}
@section('scripts')
<script>
  (function(){
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Check/Uncheck all
    document.getElementById('checkAll')?.addEventListener('change', function(e){
      document.querySelectorAll('.row-check').forEach(cb => cb.checked = e.target.checked);
    });

    // Edit: open modal and load JSON
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.addEventListener('click', async function(){
        const id = this.dataset.id;
        const res = await fetch(`{{ url('admin/daily-expences') }}/${id}`);
        if(!res.ok) return alert('Failed to load record.');
        const data = await res.json();

        document.getElementById('edit_id').value = data.expence_id;
        document.getElementById('edit_amount').value = data.amount ?? 0;
        document.getElementById('edit_comment').value = data.comment ?? '';
        document.getElementById('edit_expence_date').value = data.expence_date ?? '';
        document.getElementById('edit_iStatus').value = String(data.iStatus ?? 1);

        const typeEl = document.getElementById('edit_expence_type_id');
        if (typeEl.tagName.toLowerCase() === 'select') {
          [...typeEl.options].forEach(o => { o.selected = (String(o.value) === String(data.expence_type_id)); });
        } else {
          typeEl.value = data.expence_type_id ?? '';
        }

        const modalEl = document.getElementById('editExpenceModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      });
    });

    // Edit: submit
    document.getElementById('editForm')?.addEventListener('submit', async function(e){
      e.preventDefault();

      const id = document.getElementById('edit_id').value;
      const payload = {
        _token: csrf,
        _method: 'PUT',
        expence_type_id: document.getElementById('edit_expence_type_id').value,
        amount: document.getElementById('edit_amount').value,
        comment: document.getElementById('edit_comment').value,
        expence_date: document.getElementById('edit_expence_date').value,
        iStatus: document.getElementById('edit_iStatus').value
      };

      const res = await fetch(`{{ url('admin/daily-expences') }}/${id}`, {
        method: 'POST', // Using POST + _method=PUT for simplicity
        headers: {'Accept': 'application/json','Content-Type':'application/json','X-CSRF-TOKEN': csrf},
        body: JSON.stringify(payload)
      });

      if (!res.ok) {
        alert('Update failed. Please check required fields.');
        return;
      }
      // success
      location.reload();
    });

    // Quick toggle status
    document.querySelectorAll('.btn-toggle').forEach(btn => {
      btn.addEventListener('click', async function(){
        const id = this.dataset.id;
        const res = await fetch(`{{ url('admin/daily-expences') }}/${id}/toggle`, {
          method: 'POST',
          headers: {'X-CSRF-TOKEN': csrf, 'Accept':'application/json'}
        });
        if (!res.ok) return alert('Toggle failed');
        location.reload();
      });
    });

  })();
</script>
@endsection
@endsection
