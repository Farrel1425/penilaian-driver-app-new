<?php

namespace App\Http\Middleware;

use App\Models\Rating;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeBranchAdminReadAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role !== User::ROLE_BRANCH_ADMIN) {
            return $next($request);
        }

        abort_unless($user->branch_id, 403, 'Akun Admin Cabang belum terhubung ke unit kerja.');

        $request->merge(['branch_id' => $user->branch_id]);

        $rating = $request->route('rating');
        if ($rating instanceof Rating) {
            abort_unless($rating->branch_id === $user->branch_id, 403);
        }

        return $next($request);
    }
}