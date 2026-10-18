<?php
namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class OfficerController extends Controller
{
    public function queue(Request $r)
    {
        $u = $r->user();
        $status = $r->input('status', 'open');
        $apps = Application::with(['service', 'user'])
            ->whereHas('service', fn ($s) => $s->where('department_id', $u->department_id))
            ->when($status === 'open', fn ($x) => $x->whereIn('status', ['submitted', 'under_review']))
            ->when(! in_array($status, ['open', 'all']), fn ($x) => $x->where('status', $status))
            ->when($r->urgency, fn ($x, $v) => $x->where('urgency', $v))
            ->when($r->from, fn ($x, $d) => $x->whereDate('created_at', '>=', $d))
            ->when($r->to, fn ($x, $d) => $x->whereDate('created_at', '<=', $d))
            ->when($r->search, fn ($x, $s) => $x->where('reference', 'like', "%{$s}%"))
            ->orderByRaw("urgency = 'urgent' desc")->orderBy('created_at')
            ->paginate(15)->withQueryString();
        return view('officer.queue', compact('apps', 'status'));
    }

    public function review(Request $r, Application $application)
    {
        $this->authorizeDept($r, $application);
        if ($application->status === 'submitted') {
            $application->update(['status' => 'under_review', 'officer_id' => $r->user()->id]);
            $application->log('Review started', 'submitted', 'under_review', $r->user()->id);
        }
        $application->load('service.department', 'user', 'files', 'logs.user');
        return view('officer.review', ['a' => $application]);
    }

    public function decide(Request $r, Application $application)
    {
        $this->authorizeDept($r, $application);
        abort_unless(in_array($application->status, ['submitted', 'under_review']), 403, 'This application is not awaiting a decision.');
        $v = $r->validate([
            'action' => 'required|in:approve,revise,reject',
            'comment' => 'required_if:action,revise,reject|nullable|string|max:1500',
        ], ['comment.required_if' => 'A comment is required when requesting a revision or rejecting.']);

        $from = $application->status;
        $application->officer_id = $r->user()->id;
        $application->remarks = $v['comment'] ?? null;

        if ($v['action'] === 'approve') {
            if ((float) $application->service->fee > 0) {
                $application->status = 'pending_payment';
                $application->save();
                $label = 'Approved — awaiting payment';
            } else {
                $application->fee_amount = 0;
                $application->markApproved();
                $label = 'Approved — certificate issued';
            }
        } elseif ($v['action'] === 'revise') {
            $application->status = 'revision_requested';
            $application->save();
            $label = 'Revision requested';
        } else {
            $application->status = 'rejected';
            $application->decided_at = now();
            $application->save();
            $label = 'Rejected';
        }

        $application->log($label, $from, $application->status, $r->user()->id, $v['comment'] ?? null);
        return redirect()->route('officer.queue')->with('status', "{$application->reference}: {$label}.");
    }

    private function authorizeDept(Request $r, Application $a): void
    {
        abort_unless($r->user()->department_id && $a->service->department_id == $r->user()->department_id, 403,
            'This application belongs to another department.');
    }
}
