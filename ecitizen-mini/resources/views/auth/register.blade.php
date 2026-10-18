@extends('layouts.app')
@section('title','Register')
@section('content')
<div class="card narrow">
  <h1>Create account</h1>
  <form method="POST" action="{{ route('register') }}">@csrf
    <label>Full name<input name="name" value="{{ old('name') }}" required></label>
    <label>National ID / Passport number<input name="national_id" value="{{ old('national_id') }}" required></label>
    <label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
    <label>Phone (optional)<input name="phone" value="{{ old('phone') }}"></label>
    <label>Password<input type="password" name="password" required></label>
    <label>Confirm password<input type="password" name="password_confirmation" required></label>
    <p><button class="btn">Register &amp; send code</button></p>
  </form>
  <p class="muted">Already registered? <a href="{{ route('login') }}">Log in</a></p>
</div>
@endsection
