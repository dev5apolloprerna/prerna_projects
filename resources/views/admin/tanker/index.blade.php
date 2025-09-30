@extends('layouts.app')

@section('title', 'Tanker Master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            @include('common.alert')
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Add Tanker</h5>
                            <form method="POST" action="{{ route('tanker.store') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Tanker Name <span style="color:red;">*</span></label>
                                    <input type="text" name="tanker_name" class="form-control" value="{{ old('tanker_name') }}">
                                    @if($errors->has('tanker_name'))
                                        <span class="text-danger">{{ $errors->first('tanker_name') }}</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanker Code <span style="color:red;">*</span></label>
                                    <input type="text" name="tanker_code" class="form-control" value="{{ old('tanker_code') }}">
                                    @if($errors->has('tanker_code'))
                                        <span class="text-danger">{{ $errors->first('tanker_code') }}</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status <span style="color:red;">*</span></label>
                                    <select name="status" class="form-control">
                                        <option value="0">Inside</option>
                                        <option value="1">Outside</option>
                                    </select>
                                    @if($errors->has('status'))
                                        <span class="text-danger">{{ $errors->first('status') }}</span>
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-success">Submit</button>
                                <button type="reset" class="btn btn-light">Clear</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <form id="bulkDeleteForm" method="POST" action="{{ route('tanker.bulkDelete') }}">
                        @csrf
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Tanker List</h5>
                                <div class="d-flex">
                                    <input type="text" name="tanker_name" class="form-control me-2" placeholder="Tanker Name" value="{{ request('tanker_name') }}">
                                    <input type="text" name="tanker_code" class="form-control me-2" placeholder="Tanker Code" value="{{ request('tanker_code') }}">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                            <div class="card-body table-responsive">
                                <button type="submit" class="btn btn-danger btn-sm mb-2" onclick="return confirm('Delete selected?')"> <i class="far fa-trash-alt"></i> Bulk Delete</button>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAll"></th>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Status</th>
                                            <th>Created On</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tankers as $tanker)
                                            <tr>
                                                <td><input type="checkbox" name="ids[]" value="{{ $tanker->tanker_id }}"></td>
                                                <td>{{ $tanker->tanker_name }}</td>
                                                <td>{{ $tanker->tanker_code }}</td>
                                                <td>{{ $tanker->status == 0 ? 'Inside' : 'Outside' }}</td>
                                                <td>{{ date('d-m-Y', strtotime($tanker->created_at)) }}</td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editTankerModal"
                                                        data-id="{{ $tanker->tanker_id }}"
                                                        data-name="{{ $tanker->tanker_name }}"
                                                        data-code="{{ $tanker->tanker_code }}"
                                                        data-status="{{ $tanker->status }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>

<!--                                                     <button type="button" class="btn btn-sm btn-primary edit-btn" data-id="{{ $tanker->tanker_id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button> -->
                                                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $tanker->tanker_id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-center">
                                    {!! $tankers->links() !!}
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.tanker.edit-modal')
@endsection

@section('scripts')
<script>
    $('#selectAll').click(function() {
        $('input[name="ids[]"]').prop('checked', this.checked);
    });

    $('.delete-btn').click(function () {
        if (confirm('Delete this record?')) {
            let id = $(this).data('id');
            $.post("{{ route('tanker.delete') }}", {
                id: id,
                _token: '{{ csrf_token() }}'
            }, function (response) {
                if (response.success) {
                    location.reload();
                }
            });
        }
    });
document.addEventListener('click', function(e){
  const btn = e.target.closest('[data-bs-target="#editTankerModal"]');
  if (!btn) return;

  const id     = btn.getAttribute('data-id');
  const name   = btn.getAttribute('data-name');
  const code   = btn.getAttribute('data-code');
  const status = btn.getAttribute('data-status');

  const form = document.getElementById('editTankerForm');
  form.action = "{{ url('admin/tanker') }}/" + id; // e.g. /admin/tanker/{id}

  document.querySelector('#editTankerForm [name="tanker_id"]').value = id;
  document.querySelector('#editTankerForm [name="tanker_name"]').value = name;
  document.querySelector('#editTankerForm [name="tanker_code"]').value = code;
  document.querySelector('#editTankerForm [name="status"]').value = status;
});

</script>
@endsection