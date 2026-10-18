<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title', 'eCitizen Mini')</title>
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
<style>
:root{--g:#0b6b3a;--g2:#0e8a4b;--bg:#f4f6f5;--bd:#dde3e0;--tx:#1d2723;--mu:#6b7873}
*{box-sizing:border-box}body{margin:0;font:15px/1.5 system-ui,Segoe UI,Roboto,sans-serif;background:var(--bg);color:var(--tx)}
a{color:var(--g2)}h1{font-size:24px;margin:0 0 16px}h2{font-size:18px;margin:24px 0 10px}h3{margin:0 0 4px;font-size:16px}
.top{background:var(--g);color:#fff;display:flex;align-items:center;gap:24px;padding:12px 24px;flex-wrap:wrap}
.top a{color:#fff;text-decoration:none}.brand{font-weight:700;font-size:18px}.brand span{opacity:.7}
.top nav{display:flex;gap:16px;flex:1;flex-wrap:wrap}.top nav a{opacity:.9}.top nav a:hover{opacity:1;text-decoration:underline}
.top form{margin:0}.top button{background:none;border:1px solid #fff8;color:#fff;border-radius:6px;padding:4px 12px;cursor:pointer}
.wrap{max-width:1100px;margin:24px auto;padding:0 16px}
.card{background:#fff;border:1px solid var(--bd);border-radius:10px;padding:16px;margin-bottom:16px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px}.grid .card{margin:0}
.split{display:grid;grid-template-columns:1fr 1fr;gap:16px}@media(max-width:820px){.split{grid-template-columns:1fr}}
.muted{color:var(--mu);font-size:13px}.right{text-align:right}
label{display:block;margin:0 0 12px;font-weight:600;font-size:14px}
input,select,textarea{display:block;width:100%;padding:8px 10px;margin-top:4px;border:1px solid var(--bd);border-radius:6px;font:inherit;background:#fff}
input[type=checkbox]{display:inline;width:auto;margin:0 4px 0 0}label.inline{display:inline-flex;align-items:center;font-weight:400;margin:0}
textarea{min-height:80px}
.btn{display:inline-block;background:var(--g2);color:#fff;border:0;border-radius:6px;padding:8px 16px;cursor:pointer;text-decoration:none;font:inherit;font-weight:600}
.btn:hover{background:var(--g)}.btn.sec{background:#fff;color:var(--g);border:1px solid var(--g)}.btn.danger{background:#b3261e}.btn.warn{background:#c26a00}.btn.sm{padding:4px 10px;font-size:13px}
table{width:100%;border-collapse:collapse;background:#fff}th,td{padding:8px 10px;border-bottom:1px solid var(--bd);text-align:left;vertical-align:top}
th{font-size:12px;text-transform:uppercase;color:var(--mu);background:#fafbfa}
.badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600}
.blue{background:#e1ecff;color:#1b4fa8}.amber{background:#fff1cc;color:#8a5a00}.orange{background:#ffe3cf;color:#a14a00}
.purple{background:#ece1ff;color:#5b2aa8}.green{background:#d9f3e3;color:#0b6b3a}.red{background:#fde0de;color:#b3261e}
.alert{padding:10px 14px;border-radius:8px;margin-bottom:16px}.alert.ok{background:#d9f3e3;color:#0b5a31}.alert.err{background:#fde0de;color:#8f1d17}.alert ul{margin:0;padding-left:18px}
.filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}.filters input,.filters select{width:auto;margin:0}
.stats{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:8px}.stat{background:#fff;border:1px solid var(--bd);border-radius:10px;padding:10px 16px;min-width:130px}
.stat b{display:block;font-size:22px}.stat span{color:var(--mu);font-size:12px}
.steps{display:flex;list-style:none;padding:0;margin:0 0 16px;gap:8px;flex-wrap:wrap}
.steps li{flex:1;min-width:110px;padding:8px 12px;border-radius:8px;background:#e9edeb;color:var(--mu);font-size:13px;font-weight:600;text-align:center}
.steps li.done{background:#cfeedd;color:var(--g)}.steps li.current{background:var(--g2);color:#fff}.steps li.bad{background:#b3261e;color:#fff}
.narrow{max-width:440px;margin:40px auto}iframe{width:100%;height:560px;border:1px solid var(--bd);border-radius:8px;background:#fff}
.row{display:grid;grid-template-columns:2fr 1fr 2fr auto auto;gap:8px;align-items:center;margin-bottom:8px}.row input,.row select{margin:0}
.kv td:first-child{width:38%;color:var(--mu)}
</style>
</head>
<body>
<header class="top">
  <a class="brand" href="{{ url('/') }}">eCitizen<span>Mini</span></a>
  <nav>
  @auth
    @if(auth()->user()->role === 'citizen')
      <a href="{{ route('citizen.dashboard') }}">Services</a>
      <a href="{{ route('citizen.applications') }}">My Applications</a>
      <a href="{{ route('citizen.applications', ['status' => 'approved']) }}">Document Vault</a>
    @elseif(auth()->user()->role === 'officer')
      <a href="{{ route('officer.queue') }}">Application Queue</a>
    @elseif(auth()->user()->role === 'admin')
      <a href="{{ route('admin.dashboard') }}">Analytics</a>
      <a href="{{ route('admin.services') }}">Services</a>
      <a href="{{ route('admin.departments') }}">Departments</a>
      <a href="{{ route('admin.users') }}">Users &amp; Roles</a>
    @endif
  @endauth
  </nav>
  @auth
    <span class="muted" style="color:#fff9">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
    <form method="POST" action="{{ route('logout') }}">@csrf<button>Log out</button></form>
  @endauth
</header>
<main class="wrap">
  @if(session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="alert err"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
  @yield('content')
</main>
<script src="{{ mix('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
