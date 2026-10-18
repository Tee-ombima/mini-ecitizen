@extends('layouts.app')
@section('title','Log in')
@section('content')
<div class="card narrow">
  <h1>Log in</h1>
  <form method="POST" action="{{ route('login') }}">@csrf
    <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
    <label>Password<input type="password" name="password" required></label>
    <label class="inline"><input type="checkbox" name="remember" value="1"> Remember me</label>
    <p><button class="btn">Log in</button></p>
  </form>
  <p class="muted"><a href="{{ route('password.request') }}">Forgot password?</a></p>
  <p class="muted">New here? <a href="{{ route('register') }}">Create a citizen account</a></p>
</div>
@endsection
