{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Required only for SPA Cookie Mode (Sanctum session). Safe to keep even in token mode. --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title','App')</title>

  {{-- Your styles & JS bundle --}}
  @vite(['resources/scss/app.scss','resources/js/app.js'])

  {{-- Page-level styles hook --}}
  @stack('styles')
</head>
<body class="bg-light">

  {{-- Topbar --}}
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand fw-semibold" href="/dashboard">Eden Admin</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div id="topbarNav" class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="/categories">Categories</a></li>
          <li class="nav-item"><a class="nav-link" href="/menu">Menu</a></li>
          <li class="nav-item"><a class="nav-link" href="/orders">Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="/settings">Settings</a></li>
        </ul>
        <div class="d-flex align-items-center gap-2">
          <span id="topbarUser" class="navbar-text small text-white-50">…</span>
          <button id="btnLogout" class="btn btn-sm btn-outline-light">Logout</button>
        </div>
      </div>
    </div>
  </nav>

  {{-- Main layout with optional sidebar --}}
  <div class="container-fluid">
    <div class="row">
      {{-- (Optional) left sidebar - include if you have one --}}
      {{-- @include('partials._sidebar') --}}

      <main class="col-12 col-lg-12 py-3">
        {{-- Breadcrumbs / page heading (optional) --}}
        {{-- @include('partials._breadcrumbs') --}}

        @yield('content')
      </main>
    </div>
  </div>

  {{-- Toasts / shared modals --}}
  @includeWhen(View::exists('partials._toast'), 'partials._toast')
  @stack('modals')

  {{-- ===== Helpers: switch ONE of these blocks depending on your auth mode ===== --}}

  {{-- ============================ TOKEN MODE (using /api/login returns token) ============================ --}}
  <script>
  // ---- Token storage helpers
  function getToken(){ return localStorage.getItem('auth_token') || ''; }
  function setToken(t){ localStorage.setItem('auth_token', t); }
  function clearToken(){ localStorage.removeItem('auth_token'); }

  // ---- Generic API wrapper (adds Bearer automatically)
  async function api(url, options = {}) {
    const headers = { 'Accept':'application/json', ...(options.headers||{}) };
    const token = getToken();
    if (token) headers['Authorization'] = `Bearer ${token}`;
    if (options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';

    const res = await fetch(url, { ...options, headers, credentials: 'omit' });
    const text = await res.text();
    try {
      const json = text ? JSON.parse(text) : {};
      if (!res.ok) throw json;
      return json;
    } catch {
      throw { status: res.status, message: text };
    }
  }

  // ---- Populate topbar user; if unauthenticated, send to /login
  (async function bootTopbar(){
    try {
      const token = getToken();
      if (!token) throw new Error('no token');
      const me = await api('/api/me');
      document.getElementById('topbarUser').textContent = me?.name || me?.email || 'Signed in';
    } catch {
      document.getElementById('topbarUser').textContent = '';
      // On protected pages you can also redirect:
      // window.location.href = '/login';
    }
  })();

  // ---- Logout handler (token mode)
  document.getElementById('btnLogout')?.addEventListener('click', async ()=>{
    try { await api('/api/logout', { method:'POST' }); } catch {}
    clearToken();
    window.location.href = '/login';
  });
  </script>

  {{-- ============================ SPA COOKIE MODE (Sanctum session cookies) ============================
  <script>
  // Only if you use /sanctum/csrf-cookie + /login (session)
  async function api(url, options = {}) {
    const headers = { 'Accept':'application/json', ...(options.headers||{}) };
    if (options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';
    const res = await fetch(url, { ...options, headers, credentials: 'include' });
    const text = await res.text();
    try {
      const json = text ? JSON.parse(text) : {};
      if (!res.ok) throw json;
      return json;
    } catch {
      throw { status: res.status, message: text };
    }
  }

  (async function bootTopbar(){
    try {
      const me = await api('/api/me');
      document.getElementById('topbarUser').textContent = me?.name || me?.email || 'Signed in';
    } catch {
      document.getElementById('topbarUser').textContent = '';
      // Optionally redirect if page needs auth: window.location.href = '/login';
    }
  })();

  document.getElementById('btnLogout')?.addEventListener('click', async ()=>{
    try { await api('/api/logout', { method:'POST' }); } catch {}
    window.location.href = '/login';
  });
  </script>
  --}}

  {{-- Page-level scripts hook --}}
  @stack('scripts')
</body>
</html>
