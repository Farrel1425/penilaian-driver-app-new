<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', ['settings' => SystemSetting::values()]);
    }

    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $settings = SystemSetting::values();

        foreach ($request->safe()->except('logo') as $key => $value) {
            SystemSetting::put($key, $value);
        }

        if ($request->hasFile('logo')) {
            $oldLogo = $settings['logo'] ?? null;
            if ($oldLogo && ! Str::startsWith($oldLogo, ['http://', 'https://', '/'])) {
                Storage::disk('public')->delete($oldLogo);
            }

            SystemSetting::put('logo', $request->file('logo')->store('settings', 'public'));
        }

        return back()->with('status', 'Profil sistem berhasil diperbarui.');
    }
}
