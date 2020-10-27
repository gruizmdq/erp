<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Start Calzados</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
  <!-- Bootstrap core CSS -->
  <link href="/css/bootstrap.min.css" rel="stylesheet">
  <!-- Material Design Bootstrap -->
  <link href="/css/mdb.min.css" rel="stylesheet">
  <!-- Your custom styles (optional) -->
  <link href="/css/style.min.css" rel="stylesheet">
</head>

<body class="grey lighten-3">

  <!--Main Navigation-->
  <header>

    <!-- Sidebar -->
    <div class="sidebar-fixed position-fixed">

      <a class="logo-wrapper waves-effect">
        <img src="https://d26lpennugtm8s.cloudfront.net/stores/001/153/537/themes/common/logo-173449377-1586998124-15d8cd7eb05257244940b11efc0f25b11586998124-320-0.jpg" class="img-fluid" alt="">
      </a>

      <div class="list-group list-group-flush">
        @if (!Auth::guest() && Auth::user()->hasRole('admin'))
        <div class="list-group-item pb-0">
          <a href="/stock" class="list-group-item-action waves-effect py-1" tabindex="-1">
            <i class="fas fa-user mr-3"></i>Stock</a>
          <ul style="list-style: none">
            <li><a href="/stock/list" class="w-100 p-1 waves-effect" tabindex="-1">
              Listado</a></li>
            <li><a href="/stock/brands" class="w-100 p-1 waves-effect" tabindex="-1">
            Editar marca</a></li>
            <li><a href="/stock/articles" class="w-100 p-1 waves-effect" tabindex="-1">
            Editar artículo</a></li>
            <li><a href="/stock/colors" class="w-100 p-1 waves-effect" tabindex="-1">
            Editar color</a></li>
            <li><a href="/stock/label" class="w-100 p-1 waves-effect" tabindex="-1">
            Imprimir Etiquetas</a></li>
            <li><a href="/stock/movements" class="w-100 p-1 waves-effect" tabindex="-1">
            Movimientos</a></li>
            <li><a href="/stock/reset" class="w-100 p-1 waves-effect" tabindex="-1">
            Ajustar Stock</a></li>
          </ul>
        </div>
        
        <a href="/order" class="list-group-item list-group-item-action waves-effect" tabindex="-1">
          <i class="fas fa-table mr-3"></i>Ventas</a>
        <a href="#" class="list-group-item list-group-item-action waves-effect" tabindex="-1">
          <i class="fas fa-map mr-3"></i>Vendedores</a>
        <a href="#" class="list-group-item list-group-item-action waves-effect" tabindex="-1">
          <i class="fas fa-money-bill-alt mr-3"></i>Estadísticas</a>
        @endif
        @if (!Auth::guest() && (Auth::user()->hasRole('cashier') || Auth::user()->hasRole('admin')))
        <a href="/sell" class="list-group-item list-group-item-action waves-effect" tabindex="-1">
          <i class="fas fa-money-bill-alt mr-3"></i>Nueva orden</a>
        <a href="/cash" class="list-group-item list-group-item-action waves-effect" tabindex="-1">
          <i class="fas fa-money-bill-alt mr-3"></i>Caja</a>
        @endif

        <div class="list-group-item pb-0">
          <a href="/marketplace" class="list-group-item-action waves-effect py-1" tabindex="-1">
          <i class="fas fa-facebook mr-3"></i>Marketplace</a>
          <ul style="list-style: none">
            <li><a href="/marketplace" class="w-100 p-1 waves-effect" tabindex="-1">
            Nueva venta</a></li>
            <li><a href="/marketplace/orders" class="w-100 p-1 waves-effect" tabindex="-1">
            Ver ventas</a></li>
          </ul>
        </div>
      </div>

    </div>
    <!-- Sidebar -->

  </header>
  <!--Main Navigation-->

  <!--Main layout-->
  <main id="app" class="pt-5 mx-lg-5">
  @yield('content')
    
  </main>
  <!--Main layout-->

  <!--Footer-->
  <footer class="page-footer text-center font-small primary-color-dark darken-2 mt-4 wow fadeIn">

    <!--Call to action-->
    <div class="pt-4">
    </div>
    <!--/.Call to action-->

    <hr class="my-4">

    <!-- Social icons -->
    <div class="pb-4">
    </div>
    <!-- Social icons -->

    <!--Copyright-->
    <div class="footer-copyright py-3">
      © 2019 Copyright:
      <a href="" target="_blank"> Start Calzados </a>
    </div>
    <!--/.Copyright-->

  </footer>
  <!--/.Footer-->

  <!-- Scripts -->
  <script src="{{ asset('js/app.js') }}"></script>
  @yield('scripts')
  <script src="{{ asset('js/mdb.min.js') }}"></script>
  <script type="text/javascript">
    new WOW().init();
  </script>

</body>

</html>
