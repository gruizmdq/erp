@extends('layouts.dashboard')

@section('content')
<div class="container-fluid mt-5">

      <!-- Heading -->
      <div class="card mb-4 wow fadeIn">

        <!--Card content-->
        <div class="card-body d-sm-flex justify-content-between">

          <h4 class="mb-2 mb-sm-0 pt-1">
            <span>Ventas</span>
          </h4>
        </div>

      </div>
      <!-- Heading -->

      <!--Grid row-->
          <order-list></order-list>
    </div>
@endsection