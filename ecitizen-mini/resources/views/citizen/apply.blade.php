@extends('layouts.app')
@section('title', $service->name)
@section('content')
<h1>{{ $application ? 'Update ' . $application->reference : 'Apply: ' . $service->name }}</h1>
@if($application && $application->remarks)<div class="alert err"><b>Officer comment:</b> {{ $application->remarks }}</div>@endif
<form id="wizard" method="POST" enctype="multipart/form-data"
      action="{{ $application ? route('citizen.update', $application) : route('citizen.store', $service) }}">
  @csrf @if($application) @method('PUT') @endif
  <ol class="steps" id="stepper"><li>1. Details</li><li>2. Documents</li><li>3. Review &amp; submit</li></ol>

  <section class="card step">
    @foreach($service->fields ?? [] as $f)
      @php $nm = 'data[' . $f['name'] . ']'; $val = old('data.' . $f['name'], $application->data[$f['name']] ?? ''); $req = ! empty($f['required']); @endphp
      <label>{{ $f['label'] }}@if($req) *@endif
        @if($f['type'] === 'textarea')
          <textarea name="{{ $nm }}" data-label="{{ $f['label'] }}" @required($req)>{{ $val }}</textarea>
        @elseif($f['type'] === 'select')
          <select name="{{ $nm }}" data-label="{{ $f['label'] }}" @required($req)>
            <option value="">Select…</option>
            @foreach($f['options'] ?? [] as $o)<option value="{{ $o }}" @selected($val === $o)>{{ $o }}</option>@endforeach
          </select>
        @else
          <input type="{{ in_array($f['type'], ['number','date']) ? $f['type'] : 'text' }}" @if($f['type']==='number') step="any" @endif name="{{ $nm }}" value="{{ $val }}" data-label="{{ $f['label'] }}" @required($req)>
        @endif
      </label>
    @endforeach
    <label>Priority
      <select name="urgency" data-label="Priority">
        <option value="normal" @selected(old('urgency', $application->urgency ?? 'normal') === 'normal')>Normal</option>
        <option value="urgent" @selected(old('urgency', $application->urgency ?? '') === 'urgent')>Urgent</option>
      </select>
    </label>
    <button type="button" class="btn" data-next>Next →</button>
  </section>

  <section class="card step">
    <p class="muted">Accepted: PDF, JPG, PNG — max 5 MB each.@if($application) Leave a field empty to keep the file you uploaded before.@endif</p>
    @foreach($service->required_documents ?? [] as $i => $label)
      <label>{{ $label }}@if(! $application) *@endif
        <input type="file" name="docs[{{ $i }}]" accept=".pdf,.jpg,.jpeg,.png" data-label="{{ $label }}" @required(! $application)>
      </label>
      @if($application && ($old = $application->files->firstWhere('label', $label)))<p class="muted" style="margin-top:-8px">Current: {{ $old->original_name }}</p>@endif
    @endforeach
    <button type="button" class="btn sec" data-prev>← Back</button> <button type="button" class="btn" data-next>Next →</button>
  </section>

  <section class="card step">
    <h2 style="margin-top:0">Review your application</h2>
    <table class="kv" id="summary"></table>
    @if((float) $service->fee > 0)<p class="muted">Fee: KES {{ number_format($service->fee) }} — payable after approval.</p>@endif
    <button type="button" class="btn sec" data-prev>← Back</button> <button class="btn">{{ $application ? 'Resubmit' : 'Submit application' }}</button>
  </section>
</form>
@endsection

@push('scripts')
<script>
const steps = [...document.querySelectorAll('.step')], dots = [...document.querySelectorAll('#stepper li')];
let cur = 0;
function show(n){
  cur = n;
  steps.forEach((s,i) => s.style.display = i === n ? 'block' : 'none');
  dots.forEach((d,i) => d.className = i < n ? 'done' : (i === n ? 'current' : ''));
  if (n === 2) summary();
}
function valid(){ return [...steps[cur].querySelectorAll('input,select,textarea')].every(el => el.reportValidity()); }
function summary(){
  const t = document.getElementById('summary'); t.innerHTML = '';
  document.querySelectorAll('[data-label]').forEach(el => {
    const tr = t.insertRow(); tr.insertCell().textContent = el.dataset.label;
    tr.insertCell().textContent = el.type === 'file' ? (el.files[0] ? el.files[0].name : '(unchanged)') : el.value;
  });
}
document.querySelectorAll('[data-next]').forEach(b => b.onclick = () => valid() && show(cur + 1));
document.querySelectorAll('[data-prev]').forEach(b => b.onclick = () => show(cur - 1));
show(0);
</script>
@endpush
