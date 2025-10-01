@extends('layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            @include('common.alert')

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Vendor List</h4>
                        <div class="page-title-right">
                            <a href="{{ route('vendor.create') }}" class="btn btn-sm btn-primary">
                                <i class="far fa-plus"></i> Add New
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top bar: bulk delete + search --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <button id="btnBulkDelete" class="btn btn-sm btn-danger">
                        <i class="far fa-trash-alt"></i> Bulk Delete
                    </button>
                </div>
                <div class="col-md-6">
                    <form method="GET" class="d-flex" action="{{ route('vendor.index') }}">
                        <label class="me-2 d-flex align-items-center">Search Vendor</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Name / Contact / Email / Mobile">
                        <button class="btn btn-primary ms-2"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-striped">
                            <thead>
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" id="checkAll"></th>
                                    <th>Vendor Name</th>
                                    <th>Contact Person</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Address</th>
                                    <th>GST No.</th>
                                    <th>Created At</th> {{-- must show in list --}}
                                    <th>Status</th>
                                    <th style="width:110px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vendors as $v)
                                    <tr data-id="{{ $v->vendor_id }}">
                                        <td><input type="checkbox" class="row-check" value="{{ $v->vendor_id }}"></td>
                                        <td>{{ $v->vendor_name }}</td>
                                        <td>{{ $v->contact_person }}</td>
                                        <td>{{ $v->email }}</td>
                                        <td>{{ $v->mobile }}</td>
                                        <td>{{ $v->address }}</td>
                                        <td>{{ $v->gst_number }}</td>
                                        <td>{{ \Carbon\Carbon::parse($v->created_at)->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $v->iStatus ? 'success' : 'secondary' }} toggle-status" style="cursor:pointer" data-id="{{ $v->vendor_id }}">
                                                {{ $v->iStatus ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('vendor.edit', $v->vendor_id) }}" class="btn btn-sm btn-primary me-2" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-danger btn-sm btnDelete" title="Delete" data-id="{{ $v->vendor_id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center">No vendors found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $vendors->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    const CSRF='{{ csrf_token() }}';

    // check all
    $('#checkAll').on('change', function(){ $('.row-check').prop('checked', $(this).is(':checked')); });

    // bulk delete (hard)
    $('#btnBulkDelete').on('click', function(){
        let ids = $('.row-check:checked').map(function(){ return $(this).val(); }).get();
        if(!ids.length) return alert('Please select at least one row.');
        if(!confirm('Are you sure you want to delete selected records?')) return;

        $.ajax({
            url: "{{ route('vendor.bulk-delete') }}",
            type: 'POST',
            data: { ids: ids, _token: CSRF },
            success: function(r){ if(r.status) location.reload(); else alert('Failed to delete.'); },
            error: function(){ alert('Something went wrong.'); }
        });
    });

    // single delete (hard)
    $('.btnDelete').on('click', function(){
        let id = $(this).data('id');
        if(!confirm('Do you really want to delete this record?')) return;

        $.ajax({
            url: "{{ route('vendor.destroy', ':id') }}".replace(':id', id),
            type: 'POST',
            data: { _method: 'DELETE', _token: CSRF },
            success: function(r){ if(r.status) location.reload(); else alert('Failed to delete.'); },
            error: function(){ alert('Something went wrong.'); }
        });
    });

    // toggle status
    $('.toggle-status').on('click', function(){
        let id = $(this).data('id'), el=$(this);
        $.ajax({
            url: "{{ route('vendor.change-status', ':id') }}".replace(':id', id),
            type: 'POST',
            data: { _token: CSRF },
            success: function(r){
                if(r.status){
                    if(r.new_status==1){ el.removeClass('bg-secondary').addClass('bg-success').text('Active'); }
                    else { el.removeClass('bg-success').addClass('bg-secondary').text('Inactive'); }
                }
            }
        });
    });
});
</script>
@endpush
@endsection
