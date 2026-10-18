@extends('layouts.app')
@section('title','Application Queue')
@section('content')
<h1>Application Queue @if(auth()->user()->department)<span class="muted">— {{ auth()->user()->department->name }}</span>@endif</h1>
@unless(auth()->user()->department_id)<div class="alert err">You are not assigned to a department yet. Ask an administrator to assign one.</div>@endunless
<form method="GET" class="filters">
  <input name="search" value="{{ request('search') }}" placeholder="Reference…">
  <select name="status">
    <option value="open" @selected($status === 'open')>Open (new + in review)</option>
    <option value="all" @selected($status === 'all')>All</option>
    @foreach(\App\Models\Application::STATUSES as $k => [$l])<option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>@endforeach
  </select>
  <select name="urgency"><option value="">Any urgency</option><option value="urgent" @selected(request('urgency') === 'urgent')>Urgent</option><option value="normal" @selected(request('urgency') === 'normal')>Normal</option></select>
  <input type="date" name="from" value="{{ request('from') }}" title="From"><input type="date" name="to" value="{{ request('to') }}" title="To">
  <button class="btn">Filter</button>
</form>
<table>
  <tr><th>Reference</th><th>Service</th><th>Applicant</th><th>Urgency</th><th>Status</th><th>Submitted</th><th></th></tr>
  @forelse($apps as $a)
  <tr>
    <td>{{ $a->reference }}</td><td>{{ $a->service->name }}</td><td>{{ $a->user->name }}</td>
    <td>@if($a->urgency === 'urgent')<span class="badge red">Urgent</span>@else Normal @endif</td>
    <td><span class="badge {{ $a->status_color }}">{{ $a->status_label }}</span></td>
    <td>{{ $a->created_at->diffForHumans() }}</td>
    <td class="right"><a class="btn sm" href="{{ route('officer.review', $a) }}">Review</a></td>
  </tr>
  @empty<tr><td colspan="7" class="muted">Queue is empty.</td></tr>@endforelse
</table>
<p>{{ $apps->links() }}</p>
@endsection
