@extends('layouts.app')
@section('title', $a->reference)
@section('content')
@php
  $labels = ['Submitted', 'Under Review', 'Pending Payment', $a->status === 'rejected' ? 'Rejected' : 'Approved'];
  $pos = ['submitted' => 0, 'under_review' => 1, 'revision_requested' => 1, 'pending_payment' => 2, 'approved' => 3, 'rejected' => 3][$a->status];
@endphp
<h1>{{ $a->service->name }} <span class="badge {{ $a->status_color }}">{{ $a->status_label }}</span></h1>
<p class="muted">Reference {{ $a->reference }} · {{ $a->service->department->name }} · submitted {{ $a->created_at->format('d M Y H:i') }}</p>

<ol class="steps">
  @foreach($labels as $i => $l)
    <li class="{{ $a->status === 'rejected' && $i === 3 ? 'bad' : ($i < $pos || ($i === 3 && $a->status === 'approved') ? 'done' : ($i === $pos ? 'current' : '')) }}">{{ $l }}</li>
  @endforeach
</ol>

@if($a->remarks && in_array($a->status, ['revision_requested','rejected','pending_payment']))
  <div class="alert {{ $a->status === 'rejected' ? 'err' : 'ok' }}"><b>Officer comment:</b> {{ $a->remarks }}</div>
@endif

<div class="card">
  @if($a->status === 'revision_requested')<a class="btn warn" href="{{ route('citizen.edit', $a) }}">Edit &amp; resubmit</a>@endif
  @if($a->status === 'pending_payment')
    <form method="POST" action="{{ route('citizen.pay', $a) }}">@csrf
      <p>Your application is approved. Pay <b>KES {{ number_format($a->service->fee) }}</b> to receive your certificate.</p>
      <button class="btn">Pay now (simulated M-Pesa)</button>
    </form>
  @endif
  @if($a->status === 'approved')<a class="btn" target="_blank" href="{{ route('citizen.certificate', $a) }}">View / download certificate</a>@endif
  @if(in_array($a->status, ['submitted','under_review','rejected']))<span class="muted">No action needed right now.</span>@endif
</div>

<div class="split">
  <div class="card"><h2 style="margin-top:0">Your details</h2>
    <table class="kv">
      @foreach($a->service->fields ?? [] as $f)<tr><td>{{ $f['label'] }}</td><td>{{ $a->data[$f['name']] ?? '—' }}</td></tr>@endforeach
      <tr><td>Priority</td><td>{{ ucfirst($a->urgency) }}</td></tr>
    </table>
    <h2>Documents</h2>
    @foreach($a->files as $f)<p style="margin:4px 0">{{ $f->label }}: <a href="{{ route('files.show', $f) }}" target="_blank">{{ $f->original_name }}</a></p>@endforeach
  </div>
  <div class="card"><h2 style="margin-top:0">Activity</h2>
    <table>
      @foreach($a->logs->reverse() as $l)
        <tr><td>{{ $l->created_at->format('d M H:i') }}</td><td><b>{{ $l->action }}</b>@if($l->comment)<br><span class="muted">{{ $l->comment }}</span>@endif</td></tr>
      @endforeach
    </table>
  </div>
</div>
@endsection
