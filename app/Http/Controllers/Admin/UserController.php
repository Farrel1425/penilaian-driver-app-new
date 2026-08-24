<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['user' => new User(['status' => User::STATUS_ACTIVE])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('photo');
        $data['role'] = User::ROLE_ADMIN;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('profiles', 'public');
        }

        $user = User::query()->create($data);

        return redirect()->route('admin.users.show', $user)->with('status', 'Admin berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(Request $request, User $user): View
    {
        $returnTo = $request->string('return_to')->toString() === 'detail' ? 'detail' : 'index';

        return view('admin.users.edit', compact('user', 'returnTo'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->except('photo');

        if ($user->is(auth()->user()) && ($data['status'] ?? null) === User::STATUS_INACTIVE) {
            return back()
                ->withInput()
                ->withErrors(['status' => 'Akun yang sedang digunakan tidak dapat dinonaktifkan.']);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo && ! str_starts_with($user->photo, 'http')) {
                Storage::disk('public')->delete($user->photo);
            }

            $data['photo'] = $request->file('photo')->store('profiles', 'public');
        }

        $user->update($data);

        return $request->string('return_to')->toString() === 'detail'
            ? redirect()->route('admin.users.show', $user)->with('status', 'Admin berhasil diperbarui.')
            : redirect()->route('admin.users.index')->with('status', 'Admin berhasil diperbarui.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('status', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        if ($user->status === User::STATUS_ACTIVE && $this->activeAdminCount() <= 1) {
            return back()->with('status', 'Minimal satu admin aktif harus tersedia.');
        }

        $user->update(['status' => $user->status === User::STATUS_ACTIVE ? User::STATUS_INACTIVE : User::STATUS_ACTIVE]);

        return back()->with('status', 'Status admin berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('status', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        if ($user->status === User::STATUS_ACTIVE && $this->activeAdminCount() <= 1) {
            return back()->with('status', 'Minimal satu admin aktif harus tersedia.');
        }

        if ($user->photo && ! str_starts_with($user->photo, 'http')) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Admin berhasil dihapus.');
    }

    private function activeAdminCount(): int
    {
        return User::query()->where('role', User::ROLE_ADMIN)->where('status', User::STATUS_ACTIVE)->count();
    }
}
