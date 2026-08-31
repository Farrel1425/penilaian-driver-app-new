<?php

use App\Http\Middleware\EnsureActiveAdmin;
use App\Http\Middleware\EnsureAdminInactivity;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\ScopeBranchAdminReadAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '127.0.0.1');
        $middleware->alias([
            'active.admin' => EnsureActiveAdmin::class,
            'admin.inactivity' => EnsureAdminInactivity::class,
            'super.admin' => EnsureSuperAdmin::class,
            'branch.read' => ScopeBranchAdminReadAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();