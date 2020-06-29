@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <h3>GENERAR PDF CON LARAVEL</h3>
        <ul>
            <li>
                <a target="_blank" href="{{ action('PdfController@getGenerar',['accion'=>'ver','tipo'=>'digital']) }}">Ver PDF digital</a>
            </li>
            <li>
                <a target="_blank" href="{{ action('PdfController@getGenerar',['accion'=>'ver','tipo'=>'fisico']) }}">Ver PDF físico</a>
            </li>
            <li>
                <a target="_blank" href="{{ action('PdfController@getGenerar',['accion'=>'descargar','tipo'=>'digital']) }}">Descargar PDF digital</a>
            </li>
            <li>
                <a target="_blank" href="{{ action('PdfController@getGenerar',['accion'=>'descargar','tipo'=>'fisico']) }}">Descargar PDF físico</a>
            </li>
            <button id="buton"></button>
            <iframe onload="isLoaded()" id="pdf" name="pdf" src="http://192.168.10.10/api/pdf/generar?accion=ver&tipo=digital"></iframe></ul>
    </div>
@endsection
@section('scripts')
<script>
    
</script>
<script>
    function isLoaded()
{
  console.log('a')
  var pdfFrame = window.frames["pdf"];
  pdfFrame.focus();
  pdfFrame.print();
}


        </script>
@endsection