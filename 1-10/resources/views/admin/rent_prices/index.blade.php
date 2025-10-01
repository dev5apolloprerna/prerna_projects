@extends('layouts.app')

@section('title', 'Rent Prices')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      @include('common.alert')

      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Rent Prices</h5>
              <!-- <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#rentPriceModal">
                <i class="fa fa-plus"></i> Add Rent Price
              </button> -->
            </div>

            <div class="card-body table-responsive">
              <table class="table table-bordered align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Rent Type</th>
                    <th>Amount (₹)</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($rentPrices as $price)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $price->rent_type }}</td>
                    <td>{{ $price->amount }}</td>
                    <td>
                      @if($price->iStatus == 1)
                        <span class="badge bg-success">Active</span>
                      @else
                        <span class="badge bg-secondary">Inactive</span>
                      @endif
                    </td>
                    <td>
                      <button class="btn btn-sm btn-warning editBtn"
                              data-id="{{ $price->rent_price_id }}"
                              data-type="{{ $price->rent_type }}"
                              data-amount="{{ $price->amount }}"
                              data-status="{{ $price->iStatus }}"
                              data-bs-toggle="modal"
                              data-bs-target="#rentPriceModal">
                        <i class="fa fa-edit"></i>
                      </button>

                      <!-- <form action="{{ route('rent-prices.destroy', $price->rent_price_id) }}"
                            method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this rent price?')">
                          <i class="fa fa-trash"></i>
                        </button>
                      </form> -->
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="5" class="text-center">No records found</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
              {{ $rentPrices->links() }}
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="rentPriceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="rentPriceForm" class="modal-content">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Add Rent Price</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Rent Type <span class="text-danger">*</span></label>
          <input type="text" name="rent_type" id="rent_type" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
          <input type="number" name="amount" id="amount" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="iStatus" id="iStatus" class="form-control">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Save</button>
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>

@endsection
@section('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('rentPriceForm');
    const methodInput = document.getElementById('formMethod');
    const modalTitle = document.getElementById('modalTitle');

    document.querySelectorAll('.editBtn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const type = btn.dataset.type;
        const amount = btn.dataset.amount;
        const status = btn.dataset.status;

        form.action = "/admin/rent-prices/" + id;
        methodInput.value = "PUT";
        modalTitle.innerText = "Edit Rent Price";

        document.getElementById('rent_type').value = type;
        document.getElementById('amount').value = amount;
        document.getElementById('iStatus').value = status;
      });
    });

    // Reset form on modal open for add
    document.getElementById('rentPriceModal').addEventListener('show.bs.modal', function (e) {
      if (!e.relatedTarget.classList.contains('editBtn')) {
        form.action = "{{ route('rent-prices.store') }}";
        methodInput.value = "POST";
        modalTitle.innerText = "Add Rent Price";
        form.reset();
      }
    });
  });
</script>
@endsection
