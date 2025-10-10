@extends('layouts.app')

@section('title', 'Customer List')

@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
          
               {{-- Alert Messages --}}
            @include('common.alert')
        <div class="card"> 
          <div class="card-header">
            <h5> Customer Listing
            </h5>
          </div>
            <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('customer.index') }}" class="d-flex">
                        <input type="text" name="customer_name" class="form-control me-2" placeholder="Customer Name" value="{{ request('customer_name') }}">
                        <input type="text" name="customer_mobile" class="form-control me-2" placeholder="Mobile Number" value="{{ request('customer_mobile') }}">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ route('customer.index') }}" class="btn btn-light">Reset</a>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('customer.create') }}" class="btn btn-sm btn-primary">
                        <i class="far fa-plus"></i> Add New
                    </a>
                </div>
            </div>

            <form id="bulkDeleteForm" method="POST" action="{{ route('customer.bulkDelete') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm mb-2" onclick="return confirm('Are you sure you want to delete selected records?')"><i class="far fa-trash-alt"></i> Bulk Delete</button>
                <div class="card">
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Customer Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Customer Type</th>
                                    <th>Created On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $customer)
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" value="{{ $customer->customer_id }}"></td>
                                        <td>{{ $customer->customer_name }}</td>
                                        <td>{{ $customer->customer_mobile }}</td>
                                        <td>{{ $customer->customer_email }}</td>
                                        <td>{{ $customer->customer_address }}</td>
                                        <td>{{ $customer->customer_type }}</td>
                                        <td>{{ date('d-m-Y', strtotime($customer->created_at)) }}</td>
                                        <td>
                                            <a href="{{ route('customer.edit', $customer->customer_id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $customer->customer_id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-center mt-3">
                            {!! $customers->links() !!}
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
</div>
</div>

@endsection

@section('scripts')
<script>
    $('#selectAll').click(function() {
        $('input[name="ids[]"]').prop('checked', this.checked);
    });

    $('.delete-btn').click(function () {
        if (confirm('Are you sure you want to delete this record?')) {
            let id = $(this).data('id');
            $.post("{{ route('customer.delete') }}", {
                id: id,
                _token: '{{ csrf_token() }}'
            }, function (response) {
                if (response.success) {
                    location.reload();
                }
            });
        }
    });
</script>
@endsection
