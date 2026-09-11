<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\VehicleQrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleQrController extends Controller
{
    public function preview(Vehicle $vehicle, VehicleQrCodeService $qrCode): View
    {
        $vehicle->load('branch');

        return view('admin.vehicles.qr-preview', [
            'vehicle' => $vehicle,
            'qrDataUri' => $qrCode->dataUri($vehicle),
            'qrUrl' => $qrCode->vehicleUrl($vehicle),
        ]);
    }

    public function download(Vehicle $vehicle, VehicleQrCodeService $qrCode)
    {
        $vehicle->load('branch');
        $filename = 'qr-kendaraan-'.str($vehicle->police_number)->slug()->toString().'.pdf';

        return Pdf::loadView('admin.vehicles.qr-download', [
            'vehicle' => $vehicle,
            'qrDataUri' => $qrCode->pngDataUri($vehicle, size: 720),
        ])->setPaper('a4')->download($filename);
    }

    public function print(Request $request, Vehicle $vehicle, VehicleQrCodeService $qrCode): View
    {
        $vehicle->load('branch');

        return view('admin.vehicles.qr-print', [
            'vehicle' => $vehicle,
            'qrDataUri' => $qrCode->dataUri($vehicle, size: 420),
            'qrUrl' => $qrCode->vehicleUrl($vehicle),
            'printFormat' => $request->string('format')->toString() === 'label' ? 'label' : 'a4',
        ]);
    }
}
