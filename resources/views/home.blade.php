@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        {{--  <div class="auth-one-bg-position auth-one-bg" id="auth-particles" style="height: 600px">
            <div class="bg-overlay"></div>

            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>  --}}

        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col">

                        <div class="h-100">
                            <div class="row mb-3 pb-1">
                                <div class="col-12">
                                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                                        <div class="flex-grow-1">
                                            {{--  <h4 class="fs-16 mb-1">Admin Login</h4>  --}}
                                        </div>

                                    </div><!-- end card header -->
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->

                            <div class="row">
                                  <div class="col-md-3">
                                    <div class="card text-white bg-warning mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Total Tankers</h5>
                                            <p class="card-text fs-4">{{ $tankerCount }}</p>
                                        </div>
                                            <div class="card-footer bg-transparent border-top-0">
                                                <a href="{{ route('tanker.index') }}" class="text-white">View All</a>
                                            </div>
                                        
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card text-white bg-primary mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title text-white">In Godown Tanker</h5>
                                            <p class="card-text fs-4">{{ $intankerCount }}</p>
                                        </div>
                                        <div class="card-footer bg-transparent border-top-0">
                                        <!--<a href="{{ route('orders.index', ['isReceive' => 0]) }}" class="text-white">View All</a>-->
                                        <a href="{{ route('tankers.in-godown') }}" class="text-white">View All</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card text-white bg-primary mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title text-white">On Rent Tanker</h5>
                                            <p class="card-text fs-4">{{ $outtankerCount }}</p>
                                        </div>
                                        <div class="card-footer bg-transparent border-top-0">
                                        <a href="{{ route('orders.index', ['isReceive' => 1]) }}" class="text-white">View All</a>
                                        </div>
                                    </div>
                                </div>
                                 <!-- <div class="col-md-3">
                                    <div class="card text-white bg-info">
                                        <div class="card-body">
                                            <h5 class="card-title">Customers</h5>
                                            <p class="card-text fs-4">{{ $customerCount }}</p>
                                            <a href="{{ route('customer.index') }}" class="btn btn-light btn-sm mt-2">View All</a>
                                        </div>
                                    </div>
                                </div>
                              

                                <div class="col-md-3">
                                    <div class="card text-white bg-info">
                                        <div class="card-body">
                                            <h5 class="card-title">Total Employees</h5>
                                            <p class="card-text fs-4">{{ $employeeTotal }}</p>
                                            <a href="{{ route('employee.index') }}" class="btn btn-light btn-sm mt-2">View All</a>
                                        </div>
                                    </div>
                                </div> -->
                                <div>
                                </div>
                                <div class="col-md-3">
                                  <div class="card bg-success text-white mb-3">
                                    <div class="card-body">
                                      <h5>Total Paid</h5>
                                      <p class="fs-4">₹{{ number_format($totalPaid) }}</p>
                                      <a href="{{ route('orders.index') }}" class="btn btn-light btn-sm mt-2">View All</a>
                                    </div>
                                  </div>
                                </div>
                                
                                <div class="col-md-3">
                                  <div class="card bg-danger text-white mb-3">
                                    <div class="card-body">
                                      <h5>Total Unpaid</h5>
                                      <p class="fs-4">₹{{ number_format($totalUnpaid) }}</p>
                                      <a href="{{ route('orders.index') }}" class="btn btn-light btn-sm mt-2">View All</a>
                                    </div>
                                  </div>
                                </div>
                                  {{-- Today’s Expense --}}
                                  <div class="col-md-3">
                                    <div class="card text-white bg-info">
                                      <div class="card-body">
                                        <h5 class="card-title">Today’s Expense</h5>
                                        <p class="card-text fs-4">₹{{ number_format($todayTotal) }}</p>
                                        <a href="{{ route('admin.daily-expences.index',['preset' => 'today']) }}" class="btn btn-light btn-sm mt-2">View All</a>
                                      </div>
                                    </div>
                                  </div>

                                  {{-- This Month’s Expense --}}
                                  <div class="col-md-3">
                                    <div class="card text-white bg-success">
                                      <div class="card-body">
                                        <h5 class="card-title">This Month’s Expense</h5>
                                        <p class="card-text fs-4">₹{{ number_format($monthTotal) }}</p>
                                        <a href="{{ route('admin.daily-expences.index',['preset' => 'month']) }}" class="btn btn-light btn-sm mt-2">View All</a>
                                      </div>
                                    </div>
                                  </div>

                                
                                <div></div>
                                   <div class="col-md-3">
                                      <div class="card text-white bg-success" id="cardPresent" style="cursor:pointer">
                                        <div class="card-body">
                                          <h5 class="card-title">Present (Today)</h5>
                                          <p class="card-text fs-4">{{ $presentCount }}</p>
                                          <span class="btn btn-light btn-sm mt-2">View Names</span>
                                        </div>
                                      </div>
                                    </div>

                                    {{-- Absent --}}
                                    <div class="col-md-3">
                                      <div class="card text-white bg-danger" id="cardAbsent" style="cursor:pointer">
                                        <div class="card-body">
                                          <h5 class="card-title">Absent (Today)</h5>
                                          <p class="card-text fs-4">{{ $absentCount }}</p>
                                          <span class="btn btn-light btn-sm mt-2">View Names</span>
                                        </div>
                                      </div>
                                    </div>

                                {{-- (Optional) quick list for last few days --}}
                                {{-- <div class="col-md-6">
                                  <div class="card shadow-sm">
                                    <div class="card-body">
                                      <h6 class="text-muted mb-3">Daily Totals (This Month)</h6>
                                      <ul class="list-unstyled mb-0">
                                        @foreach($monthDailySeries as $d => $sum)
                                          <li class="d-flex justify-content-between border-bottom py-1">
                                            <span>{{ \Carbon\Carbon::parse($d)->format('d M') }}</span>
                                            <strong>₹{{ number_format($sum) }}</strong>
                                          </li>
                                        @endforeach
                                      </ul>
                                    </div>
                                  </div>
                                </div> --}}
                                
                                

                               <!--  <div class="col-md-3">
                                        <div class="card" style="background: linear-gradient(135deg, #a5b4fc, #6366f1); border:0;">
                                            <div class="card-body">
                                                <h5 class="text-white fw-bold">Total Godowns</h5>
                                                <h2> {{ number_format($godownTotal) }}</p>
                                                <a href="{{ route('godown.index') }}" class="btn btn-light btn-sm mt-2">View All</a>
                                            </div>
                                        </div>
                                </div> 
                                  <div class="col-md-3">
                                        <div class="card" style="background: linear-gradient(135deg, #66ffa6, #00c853); border:0;">
                                            <div class="card-body">
                                                <h5 class="text-dark fw-bold">Vendors</h5>
                                                <h2> {{ number_format($vendorCount) }}</p>
                                                 <a href="{{ route('vendor.index') }}" class="btn btn-light btn-sm mt-2">View All</a>
                                            </div>
                                        </div>                                    
                                 </div>

                                <div class="col-md-3">
                                            <div class="card" style="background: linear-gradient(135deg, #a5b4fc, #6366f1); border:0;">
                                                <div class="card-body">
                                                    <h5 class="text-white fw-bold">Total Orders</h5>
                                                    <p class="card-text fs-4">{{ number_format($orderCount) }}</p>
                                                     <a href="{{ route('orders.index') }}" class="btn btn-light btn-sm mt-2">View All</a>
                                                </div>
                                            </div>
                                    </div>-->

                                      {{-- Modal --}}
     
                                
                            </div>
 
                        </div>
                    </div>

                </div>

            </div>
            <!-- container-fluid -->
        </div>
        <!-- End Page-content -->


        <!--Employee list  -->
    <div class="modal fade" id="attendanceNamesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="attendanceModalTitle">Employees</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="attendanceModalBody">
              <div class="text-center text-muted">Loading...</div>
            </div>
          </div>
        </div>
      </div>

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © {{ env('APP_NAME') }}
                    </div>

                </div>
            </div>
        </footer>
    </div>
    <!-- end main content-->


@endsection

@section('scripts')
<script>
(function(){
  const today  = "{{ $today->toDateString() }}";
  const route  = "{{ route('attendance.names') }}";

  function openList(status){
    const url = `${route}?date=${encodeURIComponent(today)}&status=${encodeURIComponent(status)}`;

    // show modal immediately
    const modalEl = document.getElementById('attendanceNamesModal');
    const modal   = new bootstrap.Modal(modalEl);
    document.getElementById('attendanceModalTitle').textContent = 'Loading...';
    document.getElementById('attendanceModalBody').innerHTML = '<div class="text-center text-muted">Loading...</div>';
    modal.show();

    fetch(url, {headers: {'Accept': 'application/json'}})
      .then(r => r.json())
      .then(({title, employees}) => {
        document.getElementById('attendanceModalTitle').textContent = title;
        if (!employees.length) {
          document.getElementById('attendanceModalBody').innerHTML = '<div class="text-center text-muted">No records.</div>';
          return;
        }
        const html = `
          <ul class="list-group">
            ${employees.map(e => `<li class="list-group-item d-flex justify-content-between align-items-center">
              ${e.name}
              <span><i class="fa fa-phone"></i>: ${e.mobile}</span>
            </li>`).join('')}
          </ul>
        `;
        document.getElementById('attendanceModalBody').innerHTML = html;
      })
      .catch(() => {
        document.getElementById('attendanceModalTitle').textContent = 'Error';
        document.getElementById('attendanceModalBody').innerHTML = '<div class="text-danger">Failed to load data.</div>';
      });
  }

  document.getElementById('cardPresent')?.addEventListener('click', () => openList('P'));
  document.getElementById('cardAbsent')?.addEventListener('click', () => openList('A'));
})();
</script>
@endsection
