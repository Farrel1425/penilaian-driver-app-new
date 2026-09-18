<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Branch;
use App\Models\User;
use App\Services\PublicImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('branch:id,name')
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
        return view('admin.users.create', [
            'user' => new User(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACTIVE]),
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(UserRequest $request, PublicImageStorage $images): RedirectResponse
    {
        $data = $this->normalizedData($request);

        if ($request->hasFile('photo')) {
            $data['photo'] = $images->store($request->file('photo'), 'profiles');
        }

        try {
            $user = User::query()->create($data);
        } catch (Throwable $exception) {
            $images->delete($data['photo'] ?? null);
            throw $exception;
        }

        return redirect()->route('admin.users.show', $user)->with('status', 'Akun pengguna berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        $user->load('branch:id,name');

        return view('admin.users.show', compact('user'));
    }

    public function edit(Request $request, User $user): View
    {
        $returnTo = $request->string('return_to')->toString() === 'detail' ? 'detail' : 'index';

        return view('admin.users.edit', [
            'user' => $user,
            'returnTo' => $returnTo,
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UserRequest $request, User $user, PublicImageStorage $images): RedirectResponse
    {
        $data = $this->normalizedData($request);

        if ($user->is(auth()->user()) && ($data['status'] ?? null) === User::STATUS_INACTIVE) {
            return back()->withInput()->withErrors(['status' => 'Akun yang sedang digunakan tidak dapat dinonaktifkan.']);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $oldPhoto = null;
        $newPhoto = null;
        if ($request->hasFile('photo')) {
            $oldPhoto = $user->photo;
            $newPhoto = $data['photo'] = $images->store($request->file('photo'), 'profiles');
        } elseif ($request->boolean('remove_photo')) {
            $oldPhoto = $user->photo;
            $data['photo'] = null;
        }

        try {
            $user->update($data);
        } catch (Throwable $exception) {
            $images->delete($newPhoto);
            throw $exception;
        }
        $images->delete($oldPhoto);

        return $request->string('return_to')->toString() === 'detail'
            ? redirect()->route('admin.users.show', $user)->with('status', 'Akun pengguna berhasil diperbarui.')
            : redirect()->route('admin.users.index')->with('status', 'Akun pengguna berhasil diperbarui.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('status', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        if ($user->role === User::ROLE_ADMIN && $user->status === User::STATUS_ACTIVE && $this->activeAdminCount() <= 1) {
            return back()->with('status', 'Minimal satu admin utama aktif harus tersedia.');
        }

        $user->update(['status' => $user->status === User::STATUS_ACTIVE ? User::STATUS_INACTIVE : User::STATUS_ACTIVE]);

        return back()->with('status', 'Status akun berhasil diperbarui.');
    }

    public function destroy(User $user, PublicImageStorage $images): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('status', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }

        if ($user->role === User::ROLE_ADMIN && $user->status === User::STATUS_ACTIVE && $this->activeAdminCount() <= 1) {
            return back()->with('status', 'Minimal satu admin utama aktif harus tersedia.');
        }

        $user->delete();
        $images->delete($user->photo);

        return redirect()->route('admin.users.index')->with('status', 'Akun pengguna berhasil dihapus.');
    }

    private function normalizedData(UserRequest $request): array
    {
        $data = $request->safe()->except(['photo', 'remove_photo']);
        $data['branch_id'] = $data['role'] === User::ROLE_BRANCH_ADMIN ? $data['branch_id'] : null;

        return $data;
    }

    private function activeAdminCount(): int
    {
        return User::query()->where('role', User::ROLE_ADMIN)->where('status', User::STATUS_ACTIVE)->count();
    }
}
