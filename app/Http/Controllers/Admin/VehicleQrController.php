<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\VehicleQrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function download(Vehicle $vehicle, VehicleQrCodeService $qrCode): Response
    {
        $filename = 'qr-kendaraan-'.str($vehicle->police_number)->slug()->toString().'.svg';

        return response($qrCode->svg($vehicle), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
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
