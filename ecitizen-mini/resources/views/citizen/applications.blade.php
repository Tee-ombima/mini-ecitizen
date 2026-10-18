@extends('layouts.app')
@section('title','My Applications')
@section('content')
<h1>{{ request('status') === 'approved' ? 'Document Vault' : 'My Applications' }}</h1>
<form method="GET" class="filters">
  <select name="status" onchange="this.form.submit()"><option value="">All statuses</option>
    @foreach(\App\Models\Application::STATUSES as $k => [$l])<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach
  </select>
</form>
<table>
  <tr><th>Reference</th><th>Service</th><th>Status</th><th>Submitted</th><th></th></tr>
  @forelse($apps as $a)
  <tr>
    <td>{{ $a->reference }} @if($a->urgency === 'urgent')<span class="badge red">Urgent</span>@endif</td>
    <td>{{ $a->service->name }}</td>
    <td><span class="badge {{ $a->status_color }}">{{ $a->status_label }}</span></td>
    <td>{{ $a->created_at->format('d M Y') }}</td>
    <td class="right">
      <a class="btn sm sec" href="{{ route('citizen.show', $a) }}">Track</a>
      @if($a->status === 'revision_requested')<a class="btn sm warn" href="{{ route('citizen.edit', $a) }}">Edit</a>@endif
      @if($a->status === 'approved')<a class="btn sm" href="{{ route('citizen.certificate', $a) }}" target="_blank">Certificate</a>@endif
    </td>
  </tr>
  @empty
  <tr><td colspan="5" class="muted">Nothing here yet.</td></tr>
  @endforelse
</table>
<p>{{ $apps->links() }}</p>
@endsection
