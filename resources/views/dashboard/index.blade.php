@extends('layouts.app')
@section('title','Dashboard')

@section('content')
<div class="alert alert-info">Loading…</div>

<script>
function getToken(){ return localStorage.getItem('auth_token') || ''; }
async function api(url, options = {}) {
  const token = getToken();
  const headers = { 'Accept':'application/json', ...(options.headers||{}) };
  if (token) headers['Authorization'] = `Bearer ${token}`;
  const res = await fetch(url, { ...options, headers });
  const text = await res.text();
  try {
    const json = text ? JSON.parse(text) : {};
    if (!res.ok) throw json;
    return json;
  } catch { throw { status: res.status, message: text }; }
}

(async ()=>{
  const token = getToken();
  if (!token) return location.href = '/login';
  try {
    const me = await api('/api/me');
    // TODO: render your dashboard with "me"
    document.querySelector('.alert').outerHTML =
      `<div class="mb-3">Welcome, <strong>${me.name}</strong></div>`;
  } catch {
    localStorage.removeItem('auth_token');
    location.href = '/login';
  }
})();
</script>
@endsection
