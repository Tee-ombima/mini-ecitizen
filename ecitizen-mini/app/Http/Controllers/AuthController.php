<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showRegister() { return view('auth.register'); }
    public function showLogin() { return view('auth.login'); }

    public function register(Request $r)
    {
        $data = $r->validate([
            'name' => ['required', 'string', 'max:120'],
            'national_id' => ['required', 'string', 'regex:/^[A-Za-z0-9]{6,15}$/', 'unique:users,national_id'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], ['national_id.regex' => 'Enter a valid National ID / Passport number (6-15 letters or digits).']);

        $user = User::create([
            'name' => $data['name'], 'national_id' => strtoupper($data['national_id']), 'email' => $data['email'],
            'phone' => $data['phone'] ?? null, 'password' => $data['password'], 'role' => 'citizen',
        ]);

        $this->sendOtp($user);
        Auth::login($user);
        return redirect()->route('otp.show')->with('status', 'We sent a 6-digit code to ' . $user->email);
    }

    public function login(Request $r)
    {
        $cred = $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($cred, $r->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
        }
        $r->session()->regenerate();
        $user = $r->user();
        if (! $user->email_verified_at) {
            $this->sendOtp($user);
            return redirect()->route('otp.show')->with('status', 'Please verify your email. A new code was sent.');
        }
        return redirect($user->home());
    }

    public function showVerify(Request $r)
    {
        if ($r->user()->email_verified_at) {
            return redirect($r->user()->home());
        }
        return view('auth.verify');
    }

    public function verify(Request $r)
    {
        $r->validate(['code' => 'required|digits:6']);
        $u = $r->user();
        $ok = $u->otp_code && $u->otp_expires_at && $u->otp_expires_at->isFuture() && Hash::check($r->code, $u->otp_code);
        if (! $ok) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }
        $u->forceFill(['email_verified_at' => now(), 'otp_code' => null, 'otp_expires_at' => null])->save();
        return redirect($u->home())->with('status', 'Email verified. Welcome!');
    }

    public function resend(Request $r)
    {
        $this->sendOtp($r->user());
        return back()->with('status', 'A new code has been sent.');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function sendOtp(User $user): void
    {
        $code = (string) random_int(100000, 999999);
        $user->forceFill(['otp_code' => Hash::make($code), 'otp_expires_at' => now()->addMinutes(10)])->save();
        try {
            Mail::raw("Your eCitizen verification code is {$code}. It expires in 10 minutes.",
                fn ($m) => $m->to($user->email)->subject('Your eCitizen verification code'));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
