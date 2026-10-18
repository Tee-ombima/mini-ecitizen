@extends('layouts.app')
@section('title','Reset password')
@section('content')
<div class="card narrow">
  <h1>Reset password</h1>

  <form method="POST" action="{{ route('password.update') }}">@csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <label>Email<input type="email" name="email" value="{{ old('email', $email) }}" required autofocus></label>
    @error('email')
      <p class="error">{{ $message }}</p>
    @enderror

    <label>New password<input type="password" name="password" required></label>
    @error('password')
      <p class="error">{{ $message }}</p>
    @enderror

    <label>Confirm password<input type="password" name="password_confirmation" required></label>

    <p><button class="btn">Reset password</button></p>
  </form>
</div>
@endsection
