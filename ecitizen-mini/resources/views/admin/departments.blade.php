@extends('layouts.app')
@section('title','Departments')
@section('content')
<h1>Departments</h1>
<div class="split">
  <table><tr><th>Name</th><th>Code</th><th>Services</th><th>Officers</th></tr>
    @foreach($departments as $d)<tr><td>{{ $d->name }}</td><td>{{ $d->code }}</td><td>{{ $d->services_count }}</td><td>{{ $d->officers_count }}</td></tr>@endforeach
  </table>
  <form method="POST" action="{{ route('admin.departments.store') }}" class="card">@csrf
    <h3>Add department</h3>
    <label>Name<input name="name" required></label><label>Code<input name="code" maxlength="10" required></label>
    <button class="btn">Add</button>
  </form>
</div>
@endsection
