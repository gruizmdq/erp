@extends('layouts.dashboard')

@section('content')
<div class="container-fluid mt-5">

      <div class="card mb-4 wow fadeIn">

        <div class="card-body d-sm-flex justify-content-between">
          <h4 class="mb-2 mb-sm-0 pt-1">
            <a>Notas de Crédito</a>
          </h4>
        </div>
      </div>
      <div class="row wow fadeIn">
        <div class="col-md-12 mb-4">
          <credit-note-main></credit-note-main>
        </div>
      </div>
    </div>
@endsection