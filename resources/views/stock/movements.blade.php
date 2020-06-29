@extends('layouts.dashboard')

@section('content')
<div class="container-fluid mt-5">

      <div class="card mb-4 wow fadeIn">
        <div class="card-body d-sm-flex justify-content-between">
          <h4 class="mb-2 mb-sm-0 pt-1">
            <a href="https://mdbootstrap.com/docs/jquery/" target="_blank">Stock</a>
            <span>/</span>
            <span>Movimientos</span>
          </h4>
        </div>
      </div>

      
          <stock-movements></stock-movements>
      
      
      
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