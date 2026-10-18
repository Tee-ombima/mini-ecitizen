@extends('layouts.app')
@section('title','Verify email')
@section('content')
<div class="card narrow">
  <h1>Verify your email</h1>
  <p class="muted">Enter the 6-digit code sent to <b>{{ auth()->user()->email }}</b>. It expires in 10 minutes.</p>
  <form method="POST" action="{{ route('otp.verify') }}">@csrf
    <label>Verification code<input name="code" inputmode="numeric" maxlength="6" pattern="\d{6}" required autofocus style="letter-spacing:6px;font-size:20px"></label>
    <button class="btn">Verify</button>
  </form>
  <form method="POST" action="{{ route('otp.resend') }}" style="margin-top:12px">@csrf<button class="btn sec sm">Resend code</button></form>
</div>
@endsection
