@extends('layouts.dashboard')

@section('content')
<div class="container-fluid mt-5">

      <!-- Heading -->
      <div class="card mb-4 wow fadeIn">

        <!--Card content-->
        <div class="card-body d-sm-flex justify-content-between">

          <h4 class="mb-2 mb-sm-0 pt-1">
            <a href="https://mdbootstrap.com/docs/jquery/" target="_blank">Stock</a>
            <span>/</span>
            <span>Marcas</span>
          </h4>
        </div>

      </div>
      <!-- Heading -->

      <!--Grid row-->
      <div class="row wow fadeIn">

        <!--Grid column-->
        <div class="col-md-12 mb-4">
        <stock-brands-list></stock-brands-list>
        </div>
      </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('/js/addons/datatables.min.js') }}"></script>
<script>
  $(document).ready(function () {
  $('#table').DataTable();
  $('.dataTables_length').addClass('bs-select');
});
</script> 
@endsection