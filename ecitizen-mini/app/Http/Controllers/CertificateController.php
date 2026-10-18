<?php
namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function show(Request $r, Application $application)
    {
        abort_unless($application->user_id === $r->user()->id && $application->status === 'approved', 403);
        return view('citizen.certificate', ['a' => $application->load('service.department', 'user', 'officer')]);
    }

    public function verify(string $code)
    {
        $a = Application::with(['service.department', 'user'])
            ->where('certificate_code', $code)->where('status', 'approved')->first();
        return view('verify', compact('a', 'code'));
    }
}
