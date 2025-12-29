@extends('layouts.auth')
@section('title','Sign in')

@section('content')
<div class="card shadow-sm">
  <div class="card-body p-4">
    <h4 class="mb-3">Sign in</h4>
    <form id="loginForm" class="vstack gap-3">
      <div>
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" required>
      </div>
      <div>
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" required>
      </div>
      <div id="loginErr" class="text-danger small"></div>
      <button class="btn btn-primary w-100" type="submit">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
      </button>
    </form>
  </div>
</div>

<script>
/** Tiny API wrapper for TOKEN MODE **/
function getToken(){ return localStorage.getItem('auth_token') || ''; }
function setToken(t){ localStorage.setItem('auth_token', t); }
function clearToken(){ localStorage.removeItem('auth_token'); }

/** Generic fetch that adds Bearer if token exists */
async function api(url, options = {}) {
  const token = getToken();
  const headers = {
    'Accept': 'application/json',
    ...(options.headers || {})
  };
  if (token) headers['Authorization'] = `Bearer ${token}`;
  if (options.body && !headers['Content-Type']) {
    headers['Content-Type'] = 'application/json';
  }
  const res = await fetch(url, { credentials: 'omit', ...options, headers });
  const text = await res.text();
  try {
    const json = text ? JSON.parse(text) : {};
    if (!res.ok) throw json;
    return json;
  } catch (_) {
    throw { status: res.status, message: text };
  }
}

document.getElementById('loginForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const elErr = document.getElementById('loginErr');
  elErr.textContent = '';
  const form = e.target;
  const payload = {
    email: form.email.value.trim(),
    password: form.password.value
  };

  try {
    const data = await api('/api/login', {
      method: 'POST',
      body: JSON.stringify(payload)
    });
    // API returns: { user: {...}, token: "..." }
    setToken(data.token);
    window.location.href = '/dashboard';
  } catch (err) {
    let msg = 'Login failed';
    try {
      const j = JSON.parse(err.message || '{}');
      msg = j.message || msg;
    } catch {
      msg = 'Invalid credentials';
    }
    elErr.textContent = msg;
  }
});
</script>
@endsection
