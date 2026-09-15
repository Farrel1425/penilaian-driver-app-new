<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\VehicleQrPosterService;
use Illuminate\Http\Response;

class VehicleQrController extends Controller
{
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
