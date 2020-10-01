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
        <stock-movement-detail></stock-movement-detail>
    </div>
@endsection