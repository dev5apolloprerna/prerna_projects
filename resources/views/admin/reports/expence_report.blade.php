@extends('layouts.app')
@section('title', 'Expense Report')

@section('content')


<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      @include('common.alert')

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Daily Expense Report</h5>

          <form method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" value="{{ $start }}" class="form-control form-control-sm" />
            <input type="date" name="end_date" value="{{ $end }}" class="form-control form-control-sm" />

            <div class="col-4 d-flex gap-2">
              <button class="btn btn-primary">Search</button>
            <a href="{{ route('admin.expence-report.index') }}" class="btn btn-danger">Reset</a>
            </div>

          </form>
        </div>

        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Total Expense</th>
                  <th>Entries</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($records as $row)
                  <tr>
                    <td>{{ \Carbon\Carbon::parse($row->expence_date)->format('d M Y') }}</td>
                    <td><strong>₹{{ number_format($row->total_amount, 2) }}</strong></td>
                    <td>{{ $row->count }}</td>
                    <td>
                      <button type="button" 
                        class="btn btn-sm btn-info btn-view"
                        data-date="{{ $row->expence_date }}">
                        <i class="fa fa-eye"></i> View Details
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="text-center text-muted">No records found.</td></tr>
                @endforelse
              </tbody>

              @if($records->count())
              <tfoot>
                <tr class="table-success">
                  <th>Grand Total</th>
                  <th colspan="3">₹{{ number_format($grandTotal, 2) }}</th>
                </tr>
              </tfoot>
              @endif
            </table>
          </div>
        </div>
      </div>

      {{-- 📦 Modal: Date-wise Expense Detail --}}
        <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
              <div class="modal-header bg-gradient-primary text-white py-3">
                <h5 class="modal-title fw-semibold">
                  <i class="fa fa-calendar-day me-2"></i>Expense Details - 
                  <span id="detail_date" class="fw-bold"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body p-4" style="background-color:#f8f9fb;">
                <div id="detailLoader" class="text-center my-4" style="display:none;">
                  <div class="spinner-border text-primary" role="status"></div>
                  <div class="text-muted mt-2 small">Loading details...</div>
                </div>

                <div id="detailBody" class="fade-in"></div>
              </div>

              <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                  <i class="fa fa-times me-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="printModal">
                  <i class="fa fa-print me-1"></i> Print
                </button>
              </div>
            </div>
          </div>
        </div>


    </div>
  </div>
</div>

@section('scripts')
<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const modalEl = document.getElementById('detailModal');
  const modal = new bootstrap.Modal(modalEl);
  const detailBody = document.getElementById('detailBody');
  const detailDate = document.getElementById('detail_date');
  const loader = document.getElementById('detailLoader');

  document.querySelectorAll('.btn-view').forEach(btn => {
    btn.addEventListener('click', async function() {
      const date = this.dataset.date;
      detailDate.textContent = new Date(date).toLocaleDateString('en-GB');
      detailBody.innerHTML = '';
      loader.style.display = 'block';
      modal.show();

      try {
        const res = await fetch(`{{ url('admin/expence-report') }}/${date}`, {
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
        const html = await res.text();
        loader.style.display = 'none';
        detailBody.innerHTML = html;
      } catch (e) {
        loader.style.display = 'none';
        detailBody.innerHTML = '<div class="text-danger text-center">Failed to load details.</div>';
      }
    });
  });

  // 🖨 Print button
  document.getElementById('printModal').addEventListener('click', function(){
    const printContent = detailBody.innerHTML;
    const win = window.open('', '', 'width=900,height=700');
    win.document.write(`
      <html><head>
        <title>Expense Report</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
      </head><body class="p-4">
      <h4 class="text-center mb-3">Expense Details — ${detailDate.textContent}</h4>
      ${printContent}
      </body></html>`);
    win.document.close();
    win.print();
  });
})();

</script>
@endsection
@endsection
