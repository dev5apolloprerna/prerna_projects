@extends('layouts.app')

@section('title', 'Godowns')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            @include('common.alert')
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">

                        <div class="card-header"><strong>Add Godown</strong></div>
                            <form method="POST" action="{{ route('godown.store') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Name <span style="color:red;">*</span></label>
                                    <input type="text" class="form-control" name="Name" value="{{ old('Name') }}" placeholder="Godown Name">
                                    @if($errors->has('Name'))
                                        <span class="text-danger">{{ $errors->first('Name') }}</span>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Address <span style="color:red;">*</span></label>
                                    <input type="text" class="form-control" name="godown_address" value="{{ old('godown_address') }}" placeholder="Godown Address">
                                    @if($errors->has('godown_address'))
                                        <span class="text-danger">{{ $errors->first('godown_address') }}</span>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status <span style="color:red;">*</span></label>
                                    <select class="form-select" name="iStatus">
                                        <option value="1" {{ old('iStatus', 1) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('iStatus', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @if($errors->has('iStatus'))
                                        <span class="text-danger">{{ $errors->first('iStatus') }}</span>
                                    @endif
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success"> Save
                                    </button>
                                    <button type="reset" class="btn btn-light">Clear</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Right: Listing --}}
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Tanker List</h5>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        
                        <form method="GET" class="d-flex" action="{{ route('godown.index') }}">
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Name / Address">
                            <button class="btn btn-primary ms-2">Search</button>
                        </form>
                    </div>
                </div>

                    
                            <div class="table-responsive">
                                <div class="card-body table-responsive">
                                    <button id="btnBulkDelete" class="btn btn-sm btn-danger mb-3">
                                        <i class="far fa-trash-alt"></i> Bulk Delete
                                    </button>

                                <table class="table align-middle table-striped">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="checkAll"></th>
                                            <th>Name</th>
                                            <th>Address</th>
                                            <th>Created At</th>
                                            <th>Status</th>
                                            <th style="width:80px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($godowns as $g)
                                            <tr>
                                                <td><input type="checkbox" class="row-check" value="{{ $g->godown_id }}"></td>
                                                <td>{{ $g->Name }}</td>
                                                <td>{{ $g->godown_address }}</td>
                                                <td>{{ \Carbon\Carbon::parse($g->created_at)->format('d M Y H:i') }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $g->iStatus ? 'success' : 'secondary' }} toggle-status"
                                                          style="cursor:pointer"
                                                          data-id="{{ $g->godown_id }}">
                                                        {{ $g->iStatus ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-primary btnEdit" data-id="{{ $g->godown_id }}" title="Edit">
                                                        <i class="fas fa-edit text-white"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-danger btnDelete ms-2" data-id="{{ $g->godown_id }}" title="Delete">
                                                        <i class="fas fa-trash-alt text-white"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center">No records found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $godowns->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editGodownModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="editForm">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Godown</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div id="edit_error" class="text-danger small"></div>
            <input type="hidden" id="edit_id">
            <div class="mb-3">
                <label class="form-label">Name <span style="color:red;">*</span></label>
                <input type="text" class="form-control" id="edit_Name" name="Name">
            </div>
            <div class="mb-3">
                <label class="form-label">Address <span style="color:red;">*</span></label>
                <input type="text" class="form-control" id="edit_godown_address" name="godown_address">
            </div>
            <div class="mb-3">
                <label class="form-label">Status <span style="color:red;">*</span></label>
                <select class="form-select" id="edit_iStatus" name="iStatus">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success"> Update</button>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$(function(){
    const CSRF = '{{ csrf_token() }}';

    // ✅ Check all
    $('#checkAll').on('change', function(){
        $('.row-check').prop('checked', $(this).is(':checked'));
    });

    // ✅ Bulk delete
    $('#btnBulkDelete').on('click', function(){
        let ids = $('.row-check:checked').map(function(){ return $(this).val(); }).get();
        if (!ids.length) return alert('Select at least one row.');
        if (!confirm('Are you sure to delete selected records?')) return;

        $.post("{{ route('godown.bulk-delete') }}", { ids: ids, _token: CSRF }, function(resp){
            if(resp.status) location.reload(); else alert(resp.message);
        }).fail(()=>alert('Error deleting records.'));
    });

    // ✅ Single delete
    $('.btnDelete').on('click', function(){
        let id = $(this).data('id');
        if(!confirm('Do you really want to delete this record?')) return;

        $.post("{{ route('godown.destroy', ':id') }}".replace(':id', id), { _method:'DELETE', _token:CSRF }, function(resp){
            if(resp.status) location.reload(); else alert('Delete failed.');
        }).fail(()=>alert('Error deleting record.'));
    });

    // ✅ Load Edit Modal
    $('.btnEdit').on('click', function(){
        let id = $(this).data('id');
        $.get("{{ route('godown.show', ':id') }}".replace(':id', id), function(resp){
            if(resp.status){
                $('#edit_id').val(resp.data.godown_id);
                $('#edit_Name').val(resp.data.Name);
                $('#edit_godown_address').val(resp.data.godown_address);
                $('#edit_iStatus').val(resp.data.iStatus);
                $('#edit_error').text('');
                new bootstrap.Modal(document.getElementById('editGodownModal')).show();
            }
        }).fail(()=>alert('Failed to load data.'));
    });

    // ✅ Submit Edit
    $('#editForm').on('submit', function(e){
        e.preventDefault();
        let id = $('#edit_id').val();
        $.post("{{ route('godown.update', ':id') }}".replace(':id', id), {
            _method:'PUT', _token:CSRF,
            Name: $('#edit_Name').val(),
            godown_address: $('#edit_godown_address').val(),
            iStatus: $('#edit_iStatus').val()
        }, function(resp){
            if(resp.status) location.reload(); else $('#edit_error').text(resp.message);
        }).fail(function(xhr){
            if(xhr.status===422 && xhr.responseJSON.errors){
                let err = Object.values(xhr.responseJSON.errors)[0][0];
                $('#edit_error').text(err);
            } else $('#edit_error').text('Update failed.');
        });
    });

    // ✅ Toggle status
    $('.toggle-status').on('click', function(){
        let id = $(this).data('id'), el=$(this);
        $.post("{{ route('godown.change-status', ':id') }}".replace(':id', id), {_token:CSRF}, function(resp){
            if(resp.status){
                if(resp.new_status==1){
                    el.removeClass('bg-secondary').addClass('bg-success').text('Active');
                } else {
                    el.removeClass('bg-success').addClass('bg-secondary').text('Inactive');
                }
            }
        });
    });
});
</script>
@endsection
