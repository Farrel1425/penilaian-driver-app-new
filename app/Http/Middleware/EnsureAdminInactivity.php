<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminInactivity
{
    private const TIMEOUT_SECONDS = 1800;

    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function handle(Request $request, Closure $next): Response
    {
        $lastActivity = $request->session()->get('admin_last_activity_at');

        if ($lastActivity && Carbon::createFromTimestamp((int) $lastActivity)->addSeconds(self::TIMEOUT_SECONDS)->isPast()) {
            $user = $request->user();
            if ($user instanceof User) {
                $this->activityLogger->log($user, 'Autentikasi', 'Logout Otomatis', 'Logout otomatis setelah 30 menit tidak ada aktivitas.', $request);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Sesi Anda berakhir setelah 30 menit tidak ada aktivitas. Silakan login kembali.');
        }

        $request->session()->put('admin_last_activity_at', now()->timestamp);

        return $next($request);
    }
}