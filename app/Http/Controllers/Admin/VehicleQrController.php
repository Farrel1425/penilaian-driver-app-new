<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\VehicleQrCodeService;
use App\Services\VehicleQrPosterService;
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

    public function download(Vehicle $vehicle, VehicleQrPosterService $poster): Response
    {
        $vehicle->load('branch');
        $filename = 'qr-kendaraan-'.str($vehicle->police_number)->slug()->toString().'.png';

        return response($poster->render($vehicle), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename='.$filename,
        ]);
    }
}
