<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Usage: RoleMiddleware::class.':citizen,officer' — also forces OTP email verification. */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }
        if (! $user->email_verified_at) {
            return redirect()->route('otp.show');
        }
        abort_unless(in_array($user->role, $roles, true), 403, 'You do not have access to this area.');
        return $next($request);
    }
}
