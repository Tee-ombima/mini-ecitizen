@extends('layouts.app')
@section('title','Forgot password')
@section('content')
<div class="card narrow">
  <h1>Forgot password</h1>

  @if (session('status'))
    <div class="alert success">{{ session('status') }}</div>
  @endif

  <form method="POST" action="{{ route('password.email') }}">@csrf
    <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
    @error('email')
      <p class="error">{{ $message }}</p>
    @enderror
    <p><button class="btn">Send reset link</button></p>
  </form>

  <p class="muted"><a href="{{ route('login') }}">Back to login</a></p>
</div>
@endsection
