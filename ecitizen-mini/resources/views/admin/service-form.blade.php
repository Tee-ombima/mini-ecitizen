@extends('layouts.app')
@section('title','Service builder')
@section('content')
<h1>{{ $service ? 'Edit service' : 'New service' }}</h1>
<form method="POST" action="{{ $service ? route('admin.services.update', $service) : route('admin.services.store') }}">
  @csrf @if($service) @method('PUT') @endif
  <div class="card">
    <label>Name<input name="name" value="{{ old('name', $service->name ?? '') }}" required></label>
    <div class="split">
      <label>Category<input name="category" value="{{ old('category', $service->category ?? '') }}" required></label>
      <label>Department<select name="department_id" required>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id', $service->department_id ?? null) == $d->id)>{{ $d->name }}</option>@endforeach</select></label>
      <label>Fee (KES)<input type="number" step="0.01" min="0" name="fee" value="{{ old('fee', $service->fee ?? 0) }}" required></label>
      <label>SLA (days)<input type="number" min="1" name="sla_days" value="{{ old('sla_days', $service->sla_days ?? 5) }}" required></label>
    </div>
    <label>Description<textarea name="description">{{ old('description', $service->description ?? '') }}</textarea></label>
    <label class="inline"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active ?? true))> Active (visible to citizens)</label>
  </div>
  <div class="card"><h2 style="margin-top:0">Form fields</h2><div id="fields"></div><button type="button" class="btn sec sm" id="addField">+ Add field</button></div>
  <div class="card"><h2 style="margin-top:0">Required documents</h2><div id="docs"></div><button type="button" class="btn sec sm" id="addDoc">+ Add document</button></div>
  <button class="btn">Save service</button>
</form>
@endsection
@push('scripts')
<script>
let n = 0;
const types = ['text','textarea','number','date','select'];
function addField(f = {}) {
  const i = n++, d = document.createElement('div'); d.className = 'row';
  d.innerHTML = `<input name="fields[${i}][label]" placeholder="Field label">
    <select name="fields[${i}][type]">${types.map(t => `<option>${t}</option>`).join('')}</select>
    <input name="fields[${i}][options]" placeholder="Options, comma separated (select only)">
    <label class="inline"><input type="checkbox" name="fields[${i}][required]" value="1"> Required</label>
    <button type="button" class="btn danger sm">×</button>`;
  d.querySelector(`[name="fields[${i}][label]"]`).value = f.label || '';
  d.querySelector('select').value = f.type || 'text';
  d.querySelector(`[name="fields[${i}][options]"]`).value = (f.options || []).join(', ');
  d.querySelector('[type=checkbox]').checked = !!f.required;
  d.querySelector('button').onclick = () => d.remove();
  document.getElementById('fields').appendChild(d);
}
function addDoc(v = '') {
  const d = document.createElement('div'); d.className = 'row'; d.style.gridTemplateColumns = '1fr auto';
  d.innerHTML = `<input name="documents[]" placeholder="e.g. National ID copy"><button type="button" class="btn danger sm">×</button>`;
  d.querySelector('input').value = v; d.querySelector('button').onclick = () => d.remove();
  document.getElementById('docs').appendChild(d);
}
document.getElementById('addField').onclick = () => addField();
document.getElementById('addDoc').onclick = () => addDoc();
@json($service->fields ?? []).forEach(addField);
@json($service->required_documents ?? []).forEach(addDoc);
</script>
@endpush
