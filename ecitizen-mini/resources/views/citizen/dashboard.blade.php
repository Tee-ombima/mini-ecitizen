@extends('layouts.app')
@section('title','Service Catalog')
@section('content')
<h1>Service Catalog</h1>
<div class="stats">
  @foreach(\App\Models\Application::STATUSES as $key => [$label, $color])
    <a class="stat" style="text-decoration:none;color:inherit" href="{{ route('citizen.applications', ['status' => $key]) }}"><b>{{ $counts[$key] ?? 0 }}</b><span>{{ $label }}</span></a>
  @endforeach
</div>
<form method="GET" class="filters" style="margin-top:16px">
  <input name="q" value="{{ request('q') }}" placeholder="Search services…">
  <select name="category"><option value="">All categories</option>@foreach($categories as $c)<option @selected(request('category') === $c)>{{ $c }}</option>@endforeach</select>
  <button class="btn">Search</button>
</form>
@forelse($services->groupBy('category') as $cat => $items)
  <h2>{{ $cat }}</h2>
  <div class="grid">
    @foreach($items as $s)
      <div class="card">
        <h3>{{ $s->name }}</h3>
        <p class="muted">{{ $s->department->name }} · SLA {{ $s->sla_days }} days</p>
        <p>{{ $s->description }}</p>
        <p><b>{{ (float) $s->fee > 0 ? 'KES ' . number_format($s->fee) : 'Free' }}</b></p>
        <a class="btn" href="{{ route('citizen.apply', $s) }}">Apply</a>
      </div>
    @endforeach
  </div>
@empty
  <p class="muted">No services match your search.</p>
@endforelse
@endsection
