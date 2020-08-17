<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Material Design Bootstrap</title>
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

    <!-- Navbar -->
    <nav class="navbar fixed-top navbar-expand-lg navbar-light white scrolling-navbar">
      <div class="container-fluid">

        <!-- Brand -->
        <a class="navbar-brand waves-effect" href="https://mdbootstrap.com/docs/jquery/" target="_blank" tabindex="-1">
          <strong class="blue-text">MDB</strong>
        </a>

        <!-- Collapse -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Links -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">

          <!-- Left -->
          <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
              <a class="nav-link waves-effect" href="#" tabindex="-1">Home
                <span class="sr-only">(current)</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link waves-effect" href="https://mdbootstrap.com/docs/jquery/" target="_blank" tabindex="-1">About
                MDB</a>
            </li>
            <li class="nav-item">
              <a class="nav-link waves-effect" href="https://mdbootstrap.com/docs/jquery/getting-started/download/"
                target="_blank" tabindex="-1">Free
                download</a>
            </li>
            <li class="nav-item">
              <a class="nav-link waves-effect" href="https://mdbootstrap.com/education/bootstrap/" target="_blank" tabindex="-1">Free
                tutorials</a>
            </li>
          </ul>

          <!-- Right -->
          <ul class="navbar-nav nav-flex-icons">
            <li class="nav-item">
              <a href="https://www.facebook.com/mdbootstrap" class="nav-link waves-effect" target="_blank" tabindex="-1">
                <i class="fab fa-facebook-f"></i>
              </a>
            </li>
            <li class="nav-item">
              <a href="https://twitter.com/MDBootstrap" class="nav-link waves-effect" target="_blank" tabindex="-1">
                <i class="fab fa-twitter"></i>
              </a>
            </li>
            <li class="nav-item">
              <a href="https://github.com/mdbootstrap/bootstrap-material-design" class="nav-link border border-light rounded waves-effect"
                target="_blank"  tabindex="-1">
                <i class="fab fa-github mr-2"></i>MDB GitHub
              </a>
            </li>
          </ul>

        </div>

      </div>
    </nav>
    <!-- Navbar -->

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
          </ul>
        </div>
        
        <a href="#" class="list-group-item list-group-item-action waves-effect" tabindex="-1">
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
      <a class="btn btn-outline-white" href="https://mdbootstrap.com/docs/jquery/getting-started/download/" target="_blank"
        role="button">Download
        MDB
        <i class="fas fa-download ml-2"></i>
      </a>
      <a class="btn btn-outline-white" href="https://mdbootstrap.com/education/bootstrap/" target="_blank" role="button">Start
        free tutorial
        <i class="fas fa-graduation-cap ml-2"></i>
      </a>
    </div>
    <!--/.Call to action-->

    <hr class="my-4">

    <!-- Social icons -->
    <div class="pb-4">
      <a href="https://www.facebook.com/mdbootstrap" target="_blank">
        <i class="fab fa-facebook-f mr-3"></i>
      </a>

      <a href="https://twitter.com/MDBootstrap" target="_blank">
        <i class="fab fa-twitter mr-3"></i>
      </a>

      <a href="https://www.youtube.com/watch?v=7MUISDJ5ZZ4" target="_blank">
        <i class="fab fa-youtube mr-3"></i>
      </a>

      <a href="https://plus.google.com/u/0/b/107863090883699620484" target="_blank">
        <i class="fab fa-google-plus mr-3"></i>
      </a>

      <a href="https://dribbble.com/mdbootstrap" target="_blank">
        <i class="fab fa-dribbble mr-3"></i>
      </a>

      <a href="https://pinterest.com/mdbootstrap" target="_blank">
        <i class="fab fa-pinterest mr-3"></i>
      </a>

      <a href="https://github.com/mdbootstrap/bootstrap-material-design" target="_blank">
        <i class="fab fa-github mr-3"></i>
      </a>

      <a href="http://codepen.io/mdbootstrap/" target="_blank">
        <i class="fab fa-codepen mr-3"></i>
      </a>
    </div>
    <!-- Social icons -->

    <!--Copyright-->
    <div class="footer-copyright py-3">
      © 2019 Copyright:
      <a href="https://mdbootstrap.com/education/bootstrap/" target="_blank"> MDBootstrap.com </a>
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
