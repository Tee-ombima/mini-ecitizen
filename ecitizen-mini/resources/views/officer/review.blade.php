@extends('layouts.app')
@section('title','Review ' . $a->reference)
@section('content')
<h1>{{ $a->reference }} — {{ $a->service->name }} <span class="badge {{ $a->status_color }}">{{ $a->status_label }}</span></h1>
<div class="split">
  <div>
    <div class="card"><h2 style="margin-top:0">Applicant</h2>
      <table class="kv">
        <tr><td>Name</td><td>{{ $a->user->name }}</td></tr><tr><td>National ID</td><td>{{ $a->user->national_id }}</td></tr>
        <tr><td>Email</td><td>{{ $a->user->email }}</td></tr><tr><td>Phone</td><td>{{ $a->user->phone ?? '—' }}</td></tr>
        <tr><td>Priority</td><td>{{ ucfirst($a->urgency) }}</td></tr>
      </table>
      <h2>Form data</h2>
      <table class="kv">@foreach($a->service->fields ?? [] as $f)<tr><td>{{ $f['label'] }}</td><td>{{ $a->data[$f['name']] ?? '—' }}</td></tr>@endforeach</table>
      <h2>Uploaded files</h2>
      @foreach($a->files as $f)<p style="margin:4px 0"><a href="{{ route('files.show', $f) }}" data-view>{{ $f->label }}</a> <span class="muted">({{ $f->original_name }})</span></p>@endforeach
    </div>
    <div class="card"><h2 style="margin-top:0">Decision</h2>
      @if(in_array($a->status, ['submitted','under_review']))
      <form method="POST" action="{{ route('officer.decide', $a) }}">@csrf
        <label>Comment (required for revision / rejection)<textarea name="comment">{{ old('comment') }}</textarea></label>
        <button class="btn" name="action" value="approve">Approve</button>
        <button class="btn warn" name="action" value="revise">Request revision</button>
        <button class="btn danger" name="action" value="reject">Reject</button>
      </form>
      @else<p class="muted">No decision pending — status is “{{ $a->status_label }}”.</p>@endif
    </div>
  </div>
  <div>
    <div class="card"><h2 style="margin-top:0">Document viewer</h2>
      <iframe id="viewer" src="{{ $a->files->first() ? route('files.show', $a->files->first()) : 'about:blank' }}"></iframe>
    </div>
  </div>
</div>
<div class="card"><h2 style="margin-top:0">Audit trail</h2>
  <table><tr><th>When</th><th>Who</th><th>Action</th><th>Comment</th></tr>
  @foreach($a->logs as $l)<tr><td>{{ $l->created_at->format('d M Y H:i') }}</td><td>{{ $l->user->name ?? 'System' }}</td><td>{{ $l->action }}</td><td>{{ $l->comment }}</td></tr>@endforeach</table>
</div>
@endsection
@push('scripts')
<script>document.querySelectorAll('[data-view]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); document.getElementById('viewer').src = a.href; }));</script>
@endpush
