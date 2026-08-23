<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ReportDriverController;
use App\Http\Controllers\Admin\ReportVehicleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\VehicleQrController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Passenger\PassengerFlowController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/dashboard');

Route::prefix('rating/{vehicleToken}')->name('passenger.rating.')->group(function (): void {
    Route::get('/', [PassengerFlowController::class, 'vehicle'])->name('entry');
    Route::get('/vehicle', [PassengerFlowController::class, 'vehicle'])->name('vehicle');
    Route::get('/drivers', [PassengerFlowController::class, 'drivers'])->name('drivers');
    Route::get('/driver/{driver}', [PassengerFlowController::class, 'driver'])->name('driver');
    Route::get('/driver/{driver}/assessment', [PassengerFlowController::class, 'assessment'])->name('assessment');
    Route::post('/driver/{driver}/assessment', [PassengerFlowController::class, 'submit'])->name('submit');
    Route::get('/success/{rating}', [PassengerFlowController::class, 'success'])->name('success');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active.admin'])->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::resource('branches', BranchController::class);
        Route::patch('branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle-status');
        Route::resource('drivers', DriverController::class);
        Route::patch('drivers/{driver}/toggle-status', [DriverController::class, 'toggleStatus'])->name('drivers.toggle-status');
        Route::resource('vehicles', VehicleController::class);
        Route::get('vehicles/{vehicle}/qr', [VehicleQrController::class, 'preview'])->name('vehicles.qr.preview');
        Route::get('vehicles/{vehicle}/qr/download', [VehicleQrController::class, 'download'])->name('vehicles.qr.download');
        Route::get('vehicles/{vehicle}/qr/print', [VehicleQrController::class, 'print'])->name('vehicles.qr.print');
        Route::patch('vehicles/{vehicle}/toggle-status', [VehicleController::class, 'toggleStatus'])->name('vehicles.toggle-status');
        Route::patch('vehicles/{vehicle}/regenerate-qr', [VehicleController::class, 'regenerateQrToken'])->name('vehicles.regenerate-qr');
        Route::get('monitoring', MonitoringController::class)->name('monitoring.index');
        Route::get('reports/drivers', ReportDriverController::class)->name('reports.drivers');
        Route::get('reports/vehicles', ReportVehicleController::class)->name('reports.vehicles');
        Route::patch('questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');
        Route::resource('questions', QuestionController::class);
        Route::patch('questions/{question}/toggle-status', [QuestionController::class, 'toggleStatus'])->name('questions.toggle-status');
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});
