@extends('layouts.app')
@section('title','Analytics')
@section('content')
<h1>Analytics &amp; Reporting</h1>
<div class="stats">
  <div class="stat"><b>{{ $total }}</b><span>Total applications</span></div>
  <div class="stat"><b>KES {{ number_format($revenue) }}</b><span>Revenue collected</span></div>
  <div class="stat"><b>{{ $avgHours ?: '—' }}</b><span>Avg turnaround (hours)</span></div>
  @foreach(\App\Models\Application::STATUSES as $k => [$l])<div class="stat"><b>{{ $byStatus[$k] ?? 0 }}</b><span>{{ $l }}</span></div>@endforeach
</div>
<h2>Per-service performance</h2>
<table>
  <tr><th>Service</th><th>Department</th><th>Volume</th><th>Decided</th><th>Avg turnaround</th><th>SLA (days)</th><th>SLA breaches</th><th class="right">Revenue (KES)</th></tr>
  @foreach($rows as $r)
  <tr>
    <td>{{ $r->service->name }}</td><td>{{ $r->service->department->name }}</td><td>{{ $r->total }}</td><td>{{ $r->decided }}</td>
    <td>{{ $r->avg_hours === null ? '—' : $r->avg_hours . ' h' }}</td><td>{{ $r->service->sla_days }}</td>
    <td>@if($r->breached)<span class="badge red">{{ $r->breached }}</span>@else 0 @endif</td>
    <td class="right">{{ number_format($r->revenue) }}</td>
  </tr>
  @endforeach
</table>
@endsection
