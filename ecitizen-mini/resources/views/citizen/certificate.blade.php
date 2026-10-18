<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Certificate {{ $a->reference }}</title>
<style>
body{margin:0;background:#eee;font-family:Georgia,serif;color:#222}
.page{width:820px;max-width:96%;margin:24px auto;background:#fff;border:10px double #0b6b3a;padding:48px;text-align:center}
h1{letter-spacing:3px;color:#0b6b3a;margin:0}h2{margin:28px 0 4px;font-size:30px}.sub{color:#666}
.meta{display:flex;justify-content:space-between;align-items:flex-end;margin-top:48px;text-align:left;font-size:14px}
#qr{display:inline-block}.bar{text-align:center;margin:16px}.bar button{padding:8px 20px;font-size:15px;cursor:pointer}
@media print{body{background:#fff}.bar{display:none}.page{margin:0;width:auto;max-width:none}}
</style></head>
<body>
<div class="bar"><button onclick="window.print()">Print / Save as PDF</button></div>
<div class="page">
  <h1>REPUBLIC SERVICES PORTAL</h1>
  <div class="sub">{{ $a->service->department->name }}</div>
  <h2>{{ strtoupper($a->service->name) }}</h2>
  <p class="sub">This is to certify that</p>
  <p style="font-size:26px;margin:6px 0"><b>{{ $a->user->name }}</b></p>
  <p class="sub">ID / Passport: {{ $a->user->national_id }}</p>
  <p>has been duly processed and approved under reference <b>{{ $a->reference }}</b>.</p>
  <div class="meta">
    <div>Date issued: {{ $a->decided_at->format('d F Y') }}<br>Authorised by: {{ $a->officer->name ?? 'Department Official' }}<br>Verification code: <b>{{ $a->certificate_code }}</b></div>
    <div id="qr"></div>
  </div>
  <p class="sub" style="font-size:12px;margin-top:24px">Scan the QR code or visit {{ route('certificate.verify', $a->certificate_code) }} to verify authenticity.</p>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>new QRCode(document.getElementById('qr'), {text: @json(route('certificate.verify', $a->certificate_code)), width: 120, height: 120});</script>
</body></html>
