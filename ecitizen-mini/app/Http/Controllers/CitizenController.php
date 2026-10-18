<?php
namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationFile;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CitizenController extends Controller
{
    public function dashboard(Request $r)
    {
        $q = $r->query('q');
        $category = $r->query('category');
        $services = Service::with('department')->where('is_active', true)
            ->when($q, fn ($x) => $x->where(fn ($y) => $y->where('name', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%")))
            ->when($category, fn ($x) => $x->where('category', $category))
            ->orderBy('category')->orderBy('name')->get();
        $categories = Service::where('is_active', true)->distinct()->orderBy('category')->pluck('category');
        $counts = $r->user()->applications()->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');
        return view('citizen.dashboard', compact('services', 'categories', 'counts'));
    }

    public function apply(Service $service)
    {
        abort_unless($service->is_active, 404);
        return view('citizen.apply', ['service' => $service, 'application' => null]);
    }

    public function store(Request $r, Service $service)
    {
        abort_unless($service->is_active, 404);
        $v = $r->validate($this->rules($service, true));

        $app = DB::transaction(function () use ($r, $service, $v) {
            $app = Application::create([
                'reference' => 'EC-' . date('Y') . '-' . strtoupper(Str::random(6)),
                'user_id' => $r->user()->id, 'service_id' => $service->id,
                'urgency' => $v['urgency'], 'data' => $v['data'] ?? [], 'status' => 'submitted',
            ]);
            $this->saveFiles($app, $service, $r);
            $app->log('Application submitted', null, 'submitted', $r->user()->id);
            return $app;
        });

        return redirect()->route('citizen.show', $app)->with('status', 'Application submitted. Reference: ' . $app->reference);
    }

    public function applications(Request $r)
    {
        $apps = $r->user()->applications()->with('service')
            ->when($r->query('status'), fn ($x, $s) => $x->where('status', $s))
            ->latest()->paginate(15)->withQueryString();
        return view('citizen.applications', compact('apps'));
    }

    public function show(Request $r, Application $application)
    {
        $this->own($r, $application);
        $application->load('service.department', 'files', 'logs.user');
        return view('citizen.show', ['a' => $application]);
    }

    public function edit(Request $r, Application $application)
    {
        $this->own($r, $application);
        abort_unless($application->status === 'revision_requested', 403, 'This application cannot be edited.');
        return view('citizen.apply', ['service' => $application->service, 'application' => $application->load('files')]);
    }

    public function update(Request $r, Application $application)
    {
        $this->own($r, $application);
        abort_unless($application->status === 'revision_requested', 403);
        $service = $application->service;
        $v = $r->validate($this->rules($service, false));

        DB::transaction(function () use ($r, $application, $service, $v) {
            $application->update(['urgency' => $v['urgency'], 'data' => $v['data'] ?? [], 'status' => 'submitted']);
            $this->saveFiles($application, $service, $r);
            $application->log('Resubmitted after revision', 'revision_requested', 'submitted', $r->user()->id);
        });

        return redirect()->route('citizen.show', $application)->with('status', 'Application resubmitted.');
    }

    /** Simulated payment (swap for M-Pesa / card gateway callback in production). */
    public function pay(Request $r, Application $application)
    {
        $this->own($r, $application);
        abort_unless($application->status === 'pending_payment', 403);
        $application->fee_amount = $application->service->fee;
        $application->paid_at = now();
        $application->markApproved();
        $application->log('Payment received (simulated) — certificate issued', 'pending_payment', 'approved', $r->user()->id);
        return redirect()->route('citizen.show', $application)->with('status', 'Payment received. Your certificate is ready.');
    }

    // ---- helpers ----
    private function own(Request $r, Application $a): void
    {
        abort_unless($a->user_id === $r->user()->id, 403);
    }

    private function rules(Service $s, bool $docsRequired): array
    {
        $rules = ['urgency' => ['required', 'in:normal,urgent']];
        foreach ($s->fields ?? [] as $f) {
            $type = match ($f['type'] ?? 'text') {
                'number' => ['numeric'],
                'date' => ['date'],
                'select' => [Rule::in($f['options'] ?? [])],
                'textarea' => ['string', 'max:2000'],
                default => ['string', 'max:255'],
            };
            $rules['data.' . $f['name']] = array_merge([! empty($f['required']) ? 'required' : 'nullable'], $type);
        }
        foreach ($s->required_documents ?? [] as $i => $label) {
            $rules["docs.$i"] = [$docsRequired ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
        }
        return $rules;
    }

    private function saveFiles(Application $app, Service $s, Request $r): void
    {
        foreach ($s->required_documents ?? [] as $i => $label) {
            if (! $r->hasFile("docs.$i")) {
                continue;
            }
            $old = $app->files()->where('label', $label)->first();
            if ($old) {
                Storage::delete($old->path);
                $old->delete();
            }
            $file = $r->file("docs.$i");
            $app->files()->create([
                'label' => $label, 'path' => $file->store("applications/{$app->id}"),
                'original_name' => $file->getClientOriginalName(), 'mime' => $file->getClientMimeType(), 'size' => $file->getSize(),
            ]);
        }
    }
}
