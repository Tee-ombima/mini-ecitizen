<?php
namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Department;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $rows = Service::with('department')->get()->map(function ($s) {
            $apps = Application::where('service_id', $s->id)->get();
            $done = $apps->filter(fn ($a) => $a->decided_at);
            $hours = $done->avg(fn ($a) => abs($a->created_at->diffInHours($a->decided_at)));
            $breached = $done->filter(fn ($a) => abs($a->created_at->diffInDays($a->decided_at)) > $s->sla_days)->count();
            return (object) [
                'service' => $s, 'total' => $apps->count(), 'decided' => $done->count(),
                'avg_hours' => $hours === null ? null : round($hours, 1), 'breached' => $breached,
                'revenue' => (float) $apps->whereNotNull('paid_at')->sum('fee_amount'),
            ];
        });
        $byStatus = Application::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        return view('admin.dashboard', [
            'rows' => $rows, 'byStatus' => $byStatus,
            'total' => $rows->sum('total'), 'revenue' => $rows->sum('revenue'),
            'avgHours' => round((float) $rows->whereNotNull('avg_hours')->avg('avg_hours'), 1),
        ]);
    }

    // ---- Service builder ----
    public function services() { return view('admin.services', ['services' => Service::with('department')->orderBy('category')->get()]); }

    public function createService()
    {
        return view('admin.service-form', ['service' => null, 'departments' => Department::orderBy('name')->get()]);
    }

    public function storeService(Request $r)
    {
        Service::create($this->payload($r));
        return redirect()->route('admin.services')->with('status', 'Service created.');
    }

    public function editService(Service $service)
    {
        return view('admin.service-form', ['service' => $service, 'departments' => Department::orderBy('name')->get()]);
    }

    public function updateService(Request $r, Service $service)
    {
        $service->update($this->payload($r));
        return redirect()->route('admin.services')->with('status', 'Service updated.');
    }

    private function payload(Request $r): array
    {
        $d = $r->validate([
            'name' => 'required|string|max:150', 'category' => 'required|string|max:80',
            'department_id' => 'required|exists:departments,id', 'description' => 'nullable|string|max:1000',
            'fee' => 'required|numeric|min:0', 'sla_days' => 'required|integer|min:1|max:90',
            'fields' => 'nullable|array', 'fields.*.label' => 'nullable|string|max:100',
            'fields.*.type' => 'nullable|in:text,textarea,number,date,select', 'fields.*.options' => 'nullable|string|max:500',
            'documents' => 'nullable|array', 'documents.*' => 'nullable|string|max:100',
        ]);

        $used = [];
        $fields = collect($d['fields'] ?? [])->filter(fn ($f) => filled($f['label'] ?? null))->values()
            ->map(function ($f, $i) use (&$used) {
                $name = Str::slug($f['label'], '_') ?: 'field_' . $i;
                while (in_array($name, $used)) { $name .= '_' . $i; }
                $used[] = $name;
                return [
                    'name' => $name, 'label' => $f['label'], 'type' => $f['type'] ?? 'text', 'required' => ! empty($f['required']),
                    'options' => array_values(array_filter(array_map('trim', explode(',', $f['options'] ?? '')))),
                ];
            })->all();

        return [
            'name' => $d['name'], 'category' => $d['category'], 'department_id' => $d['department_id'],
            'description' => $d['description'] ?? null, 'fee' => $d['fee'], 'sla_days' => $d['sla_days'],
            'fields' => $fields, 'required_documents' => array_values(array_filter($d['documents'] ?? [])),
            'is_active' => $r->boolean('is_active'),
        ];
    }

    // ---- Departments & RBAC ----
    public function departments() { return view('admin.departments', ['departments' => Department::withCount(['services', 'officers'])->get()]); }

    public function storeDepartment(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:120', 'code' => 'required|string|max:10|unique:departments,code']);
        Department::create(['name' => $d['name'], 'code' => strtoupper($d['code'])]);
        return back()->with('status', 'Department added.');
    }

    public function users(Request $r)
    {
        $users = User::with('department')->when($r->role, fn ($x, $v) => $x->where('role', $v))
            ->when($r->search, fn ($x, $s) => $x->where(fn ($y) => $y->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
            ->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.users', ['users' => $users, 'departments' => Department::orderBy('name')->get()]);
    }

    public function updateUser(Request $r, User $user)
    {
        $d = $r->validate(['role' => 'required|in:citizen,officer,admin', 'department_id' => 'nullable|exists:departments,id']);
        if ($user->id === $r->user()->id && $d['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own admin role.']);
        }
        if ($d['role'] === 'officer' && empty($d['department_id'])) {
            return back()->withErrors(['department_id' => 'Officers must be assigned to a department.']);
        }
        $user->update(['role' => $d['role'], 'department_id' => $d['role'] === 'officer' ? $d['department_id'] : null]);
        return back()->with('status', "{$user->name} updated.");
    }
}
