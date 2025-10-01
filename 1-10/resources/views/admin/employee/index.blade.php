@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Alert Messages --}}
            @include('common.alert')
 <div class="card"> 
          <div class="card-header">
            <h5> Employee List
            </h5>
          </div>
            <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" class="d-flex" action="{{ route('employee.index') }}">
                        <input type="text" class="form-control me-2" name="search" value="{{ request('search') }}" placeholder="Name / Designation / Mobile">
                        <button type="submit" class="btn btn-primary gap-2">Search</button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                     <a href="{{ route('employee.create') }}" class="btn btn-sm btn-primary">
                        <i class="far fa-plus"></i> Add New
                    </a>
                </div>
            </div>

            

            {{-- Top Bar: Bulk delete (left) & Search (right) --}}
            <div class="row mb-3">
                <div class="col-md-6 d-flex align-items-center gap-2">
                    <button id="btnBulkDelete" class="btn btn-sm btn-danger">
                        <i class="far fa-trash-alt"></i> Bulk Delete
                    </button>
                </div>
            </div>

            {{-- Listing Card --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-striped">
                            <thead>
                                <tr>
                                    <th style="width:40px;">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Mobile</th>
                                    <th>Address</th>
                                    <th>Created At {{-- per rule, show created_at in list --}}</th>
                                    <th>Status</th>
                                    <th style="width:110px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $emp)
                                    <tr data-id="{{ $emp->emp_id }}">
                                        <td>
                                            <input type="checkbox" class="row-check" value="{{ $emp->emp_id }}">
                                        </td>
                                        <td>{{ $emp->name }}</td>
                                        <td>{{ $emp->designation }}</td>
                                        <td>{{ $emp->mobile }}</td>
                                        <td>{{ $emp->address }}</td>
                                        <td>{{ \Carbon\Carbon::parse($emp->created_at)->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $emp->iStatus ? 'success' : 'secondary' }} toggle-status" style="cursor:pointer" data-id="{{ $emp->emp_id }}">
                                                {{ $emp->iStatus ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('employee.edit', $emp->emp_id) }}" class="btn btn-primary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-light btn-sm btnDelete" title="Delete" data-id="{{ $emp->emp_id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">No employees found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        const CSRF = '{{ csrf_token() }}';

        // ✅ Check/Uncheck All
        $('#checkAll').on('change', function () {
            $('.row-check').prop('checked', $(this).is(':checked'));
        });

        // ✅ Bulk Delete
        $('#btnBulkDelete').on('click', function () {
            let ids = $('.row-check:checked').map(function () { return $(this).val(); }).get();
            if (ids.length === 0) {
                alert('Please select at least one row.');
                return;
            }
            if (!confirm('Are you sure you want to delete selected records?')) return;

            $.ajax({
                url: "{{ route('employee.bulk-delete') }}",
                type: "POST",
                data: { ids: ids, _token: CSRF },
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        alert('Failed to delete.');
                    }
                },
                error: function () {
                    alert('Something went wrong.');
                }
            });
        });

        // ✅ Single Delete
        $('.btnDelete').on('click', function () {
            let id = $(this).data('id');
            if (!confirm('Do you really want to delete this record?')) return;

            $.ajax({
                url: "{{ route('employee.destroy', ':id') }}".replace(':id', id),
                type: "POST",
                data: { _method: 'DELETE', _token: CSRF },
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        alert('Failed to delete.');
                    }
                },
                error: function () {
                    alert('Something went wrong.');
                }
            });
        });

        // ✅ Toggle Status
        $('.toggle-status').on('click', function () {
            let id = $(this).data('id');
            let el = $(this);

            $.ajax({
                url: "{{ route('employee.change-status', ':id') }}".replace(':id', id),
                type: "POST",
                data: { _token: CSRF },
                success: function (data) {
                    if (data.status) {
                        if (data.new_status == 1) {
                            el.removeClass('bg-secondary').addClass('bg-success').text('Active');
                        } else {
                            el.removeClass('bg-success').addClass('bg-secondary').text('Inactive');
                        }
                    }
                },
                error: function () {
                    alert('Status change failed.');
                }
            });
        });
    });
</script>
@endsection
