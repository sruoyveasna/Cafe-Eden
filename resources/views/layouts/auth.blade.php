<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Login')</title>
  @vite(['resources/scss/app.scss','resources/js/app.js'])
</head>
<body class="min-vh-100 d-flex align-items-center">
  <main class="container" style="max-width:460px">
    @yield('content')
  </main>
</body>
</html>
