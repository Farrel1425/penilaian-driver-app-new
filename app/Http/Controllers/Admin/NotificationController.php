<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function markAsRead(Request $request): Response
    {
        $user = $request->user();

        if ($user) {
            $user->forceFill([
                'notifications_read_at' => now(),
            ])->save();
        }

        return response()->noContent();
    }
}
