<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['status' => User::STATUS_ACTIVE], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $activityLogger->log($request->user(), 'Autentikasi', 'Login', 'Login ke sistem berhasil.', $request);

        return redirect()->intended(route('admin.dashboard'))->with('status', 'Selamat datang kembali.');
    }

    public function destroy(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $activityLogger->log($request->user(), 'Autentikasi', 'Logout', 'Logout dari sistem.', $request);
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda berhasil logout.');
    }
}
