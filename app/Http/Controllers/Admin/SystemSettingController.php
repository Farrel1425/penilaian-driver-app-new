<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;
use App\Models\SystemSetting;
use App\Services\PublicImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class SystemSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', ['settings' => SystemSetting::values()]);
    }

    public function update(UpdateSystemSettingsRequest $request, PublicImageStorage $images): RedirectResponse
    {
        $settings = SystemSetting::values();

        foreach ($request->safe()->except(['logo', 'remove_logo']) as $key => $value) {
            SystemSetting::put($key, $value);
        }

        if ($request->hasFile('logo')) {
            $oldLogo = $settings['logo'] ?? null;
            $newLogo = $images->store($request->file('logo'), 'settings');
            try {
                SystemSetting::put('logo', $newLogo);
            } catch (Throwable $exception) {
                $images->delete($newLogo);
                throw $exception;
            }
            $images->delete($oldLogo);
        } elseif ($request->boolean('remove_logo')) {
            $oldLogo = $settings['logo'] ?? null;
            SystemSetting::put('logo', null);
            $images->delete($oldLogo);
        }

        return back()->with('status', 'Profil sistem berhasil diperbarui.');
    }
}
