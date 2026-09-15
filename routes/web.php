<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\EmployeeCategoryController;
use App\Http\Controllers\Admin\IndicatorCategoryController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OperationalMonitoringController;
use App\Http\Controllers\Admin\PartnershipInquiryController as AdminPartnershipInquiryController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ReportBranchController;
use App\Http\Controllers\Admin\ReportDriverController;
use App\Http\Controllers\Admin\ReportExportController;
use App\Http\Controllers\Admin\ReportVehicleController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\VehicleQrController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\PartnershipInquiryController;
use App\Http\Controllers\Passenger\PassengerFlowController;
use App\Http\Middleware\LogAdminActivity;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::post('/partnership-inquiries', [PartnershipInquiryController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('partnership-inquiries.store');

Route::prefix('rating/{vehicleToken}')->name('passenger.rating.')->group(function (): void {
    Route::get('/', [PassengerFlowController::class, 'vehicle'])->name('entry');
    Route::get('/vehicle', [PassengerFlowController::class, 'vehicle'])->name('vehicle');
    Route::get('/drivers', [PassengerFlowController::class, 'drivers'])->name('drivers');
    Route::get('/driver/{driver}', [PassengerFlowController::class, 'driver'])->name('driver');
    Route::get('/driver/{driver}/assessor', [PassengerFlowController::class, 'assessor'])->name('assessor');
    Route::post('/driver/{driver}/assessor', [PassengerFlowController::class, 'storeAssessor'])->name('assessor.store');
    Route::get('/driver/{driver}/assessment', [PassengerFlowController::class, 'assessment'])->name('assessment');
    Route::post('/driver/{driver}/assessment', [PassengerFlowController::class, 'submit'])->name('submit');
    Route::get('/success/{rating}', [PassengerFlowController::class, 'success'])->name('success');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'createLinkRequest'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'storeLinkRequest'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'createReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'storeReset'])->name('password.update');
});

Route::middleware(['auth', 'active.admin', 'admin.inactivity', LogAdminActivity::class])->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('/admin/session/ping', fn () => response()->noContent())->name('admin.session.ping');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::middleware('branch.read')->group(function (): void {
            Route::post('notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
            Route::get('/dashboard', DashboardController::class)->name('dashboard');
            Route::get('penilaian/riwayat', [MonitoringController::class, 'index'])->name('assessments.index');
            Route::get('penilaian/riwayat/export', [MonitoringController::class, 'export'])->name('assessments.export');
            Route::get('penilaian/riwayat/{rating}', [MonitoringController::class, 'show'])->name('assessments.show');
            Route::get('penilaian/rekap', [MonitoringController::class, 'recap'])->name('assessments.recap');
            Route::get('penilaian/monitoring', [OperationalMonitoringController::class, 'index'])->name('monitoring.index');
            Route::get('penilaian/monitoring/report', [OperationalMonitoringController::class, 'report'])->name('monitoring.report');
            Route::get('penilaian/monitoring/export', [OperationalMonitoringController::class, 'export'])->name('monitoring.export');
            Route::get('penilaian/monitoring/{branch}', [OperationalMonitoringController::class, 'show'])->name('monitoring.show');
            Route::get('penilaian/monitoring/{branch}/report', [OperationalMonitoringController::class, 'report'])->name('monitoring.branch.report');
            Route::get('penilaian/monitoring/{branch}/export', [OperationalMonitoringController::class, 'export'])->name('monitoring.branch.export');
            Route::get('penilaian/monitoring/{branch}/drivers/{driver}', [OperationalMonitoringController::class, 'driver'])->name('monitoring.driver');
            Route::post('penilaian/monitoring/{branch}/drivers/{driver}/attendance', [OperationalMonitoringController::class, 'storeAttendance'])->name('monitoring.attendance.store');
            Route::get('reports/drivers', ReportDriverController::class)->name('reports.drivers');
            Route::get('reports/vehicles', ReportVehicleController::class)->name('reports.vehicles');
            Route::get('reports/branches', ReportBranchController::class)->name('reports.branches');
            Route::get('reports/{type}/export', [ReportExportController::class, 'excel'])->name('reports.export');
            Route::get('reports/{type}/pdf', [ReportExportController::class, 'pdf'])->name('reports.pdf');
            Route::get('reports/{type}/print', [ReportExportController::class, 'print'])->name('reports.print');
        });

        Route::middleware('super.admin')->group(function (): void {
            Route::get('partnership-inquiries', [AdminPartnershipInquiryController::class, 'index'])->name('partnership-inquiries.index');
            Route::get('partnership-inquiries/{partnershipInquiry}', [AdminPartnershipInquiryController::class, 'show'])->name('partnership-inquiries.show');
            Route::patch('partnership-inquiries/{partnershipInquiry}/status', [AdminPartnershipInquiryController::class, 'updateStatus'])->name('partnership-inquiries.status');
            Route::resource('branches', BranchController::class);
            Route::patch('branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle-status');
            Route::resource('employees', DriverController::class)->parameters(['employees' => 'driver']);
            Route::patch('employees/{driver}/toggle-status', [DriverController::class, 'toggleStatus'])->name('employees.toggle-status');
            Route::resource('drivers', DriverController::class)->parameters(['drivers' => 'driver']);
            Route::patch('drivers/{driver}/toggle-status', [DriverController::class, 'toggleStatus'])->name('drivers.toggle-status');
            Route::resource('employee-categories', EmployeeCategoryController::class)->except('show');
            Route::patch('employee-categories/{employee_category}/toggle-status', [EmployeeCategoryController::class, 'toggleStatus'])->name('employee-categories.toggle-status');
            Route::resource('indicator-categories', IndicatorCategoryController::class)->except('show');
            Route::patch('indicator-categories/{indicator_category}/toggle-status', [IndicatorCategoryController::class, 'toggleStatus'])->name('indicator-categories.toggle-status');
            Route::resource('vehicles', VehicleController::class);
            Route::get('vehicles/{vehicle}/qr', [VehicleQrController::class, 'preview'])->name('vehicles.qr.preview');
            Route::get('vehicles/{vehicle}/qr/download', [VehicleQrController::class, 'download'])->name('vehicles.qr.download');
            Route::patch('vehicles/{vehicle}/toggle-status', [VehicleController::class, 'toggleStatus'])->name('vehicles.toggle-status');
            Route::patch('vehicles/{vehicle}/regenerate-qr', [VehicleController::class, 'regenerateQrToken'])->name('vehicles.regenerate-qr');
            Route::patch('questions/reorder', [QuestionController::class, 'reorder'])->name('questions.reorder');
            Route::resource('questions', QuestionController::class);
            Route::patch('questions/{question}/toggle-status', [QuestionController::class, 'toggleStatus'])->name('questions.toggle-status');
            Route::resource('users', UserController::class);
            Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::get('settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SystemSettingController::class, 'update'])->name('settings.update');
            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
            Route::get('activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
        });
    });
});
