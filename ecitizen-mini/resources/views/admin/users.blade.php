@extends('layouts.app')
@section('title','Users & Roles')
@section('content')
<h1>Users &amp; Roles</h1>
<form method="GET" class="filters">
  <input name="search" value="{{ request('search') }}" placeholder="Name or email…">
  <select name="role"><option value="">All roles</option>@foreach(['citizen','officer','admin'] as $r)<option @selected(request('role') === $r)>{{ $r }}</option>@endforeach</select>
  <button class="btn">Filter</button>
</form>
<p class="muted">Officers only see applications for the department they are assigned to.</p>
<table>
  <tr><th>User</th><th>Role</th><th>Department</th><th></th></tr>
  @foreach($users as $u)
  <tr>
    <td>{{ $u->name }}<br><span class="muted">{{ $u->email }}</span></td>
    <td colspan="3">
      <form method="POST" action="{{ route('admin.users.update', $u) }}" style="display:flex;gap:8px;align-items:center">@csrf @method('PATCH')
        <select name="role" style="margin:0">@foreach(['citizen','officer','admin'] as $r)<option @selected($u->role === $r)>{{ $r }}</option>@endforeach</select>
        <select name="department_id" style="margin:0"><option value="">—</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected($u->department_id === $d->id)>{{ $d->name }}</option>@endforeach</select>
        <button class="btn sm">Save</button>
      </form>
    </td>
  </tr>
  @endforeach
</table>
<p>{{ $users->links() }}</p>
@endsection
