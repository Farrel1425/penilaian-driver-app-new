<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BranchAdminSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = (string) env('BRANCH_ADMIN_SEEDER_PASSWORD', 'GantiPassword!2026');

        Branch::query()
            ->orderBy('id')
            ->each(function (Branch $branch) use ($defaultPassword): void {
                $email = $this->emailFor($branch);
                $user = User::query()->firstOrNew(['email' => $email]);
                $isNewUser = ! $user->exists;

                $user->fill([
                    'name' => Str::startsWith(Str::lower($branch->name), 'cabang ')
                        ? "Admin {$branch->name}"
                        : "Admin Cabang {$branch->name}",
                    'role' => User::ROLE_BRANCH_ADMIN,
                    'branch_id' => $branch->id,
                    'status' => $branch->status === Branch::STATUS_ACTIVE
                        ? User::STATUS_ACTIVE
                        : User::STATUS_INACTIVE,
                ]);

                if ($isNewUser) {
                    $user->password = Hash::make($defaultPassword);
                }

                $user->save();
            });
    }

    private function emailFor(Branch $branch): string
    {
        $email = Str::lower(trim((string) $branch->email));

        return filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $email
            : 'admin.'.Str::lower(Str::replace('-', '', $branch->code)).'@example.com';
    }
}