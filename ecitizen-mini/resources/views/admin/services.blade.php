@extends('layouts.app')
@section('title','Services')
@section('content')
<h1>Services <a class="btn sm" href="{{ route('admin.services.create') }}">+ New service</a></h1>
<table>
  <tr><th>Name</th><th>Category</th><th>Department</th><th>Fee</th><th>SLA</th><th>Fields</th><th>Status</th><th></th></tr>
  @foreach($services as $s)
  <tr><td>{{ $s->name }}</td><td>{{ $s->category }}</td><td>{{ $s->department->name }}</td><td>{{ number_format($s->fee) }}</td><td>{{ $s->sla_days }}d</td>
    <td>{{ count($s->fields ?? []) }} fields, {{ count($s->required_documents ?? []) }} docs</td>
    <td><span class="badge {{ $s->is_active ? 'green' : 'red' }}">{{ $s->is_active ? 'Active' : 'Hidden' }}</span></td>
    <td class="right"><a class="btn sm sec" href="{{ route('admin.services.edit', $s) }}">Edit</a></td></tr>
  @endforeach
</table>
@endsection
