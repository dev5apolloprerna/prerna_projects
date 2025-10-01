@extends('layouts.app')
@section('title','In Godown Tankers')

@section('content')
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">

      @include('common.alert')

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">In Godown Tankers</h5>
          <span class="badge bg-secondary">Total: {{ $tankers->total() }}</span>
        </div>

        <div class="card-body">
          <form method="GET" action="{{ route('tankers.in-godown') }}" class="row g-2 mb-3">
            <div class="col-md-4">
              <input type="text" name="search" value="{{ request('search') }}"
                     class="form-control" placeholder="Search code, name, location">
            </div>
            <div class="col-md-3">
              <button class="btn btn-primary">Search</button>
              <a href="{{ route('tankers.in-godown') }}" class="btn btn-light ms-1">Reset</a>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th style="width:60px;">#</th>
                  <th>Tanker No</th>
                  <th>Tanker Name</th>
                  <th>Godown</th>
                  <th>Status</th>
                  <th>Received At</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tankers as $i => $t)
                  <tr>
                    <td>{{ $tankers->firstItem() + $i }}</td>
                    <td>{{ $t->tanker_code ?? '-' }}</td>
                    <td>{{ $t->tanker_name ?? '-' }}</td>
                    <td>{{ $t->godown->Name ?? '-' }}</td>
                    <td><span class="badge bg-info">In Godown</span></td>
                    <td>
                    {{ $t->order?->received_at?->format('d-m-Y') ?? $t->created_at?->format('d-m-Y') ?? '-' }}
                    </td>
                  </tr
                @empty
                  <tr>
                    <td colspan="6" class="text-center">No tankers in godown.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-center mt-2">
            {{ $tankers->links() }}
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
