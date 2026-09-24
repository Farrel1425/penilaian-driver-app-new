<?php

namespace App\Services;

use App\Models\Vehicle;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

class VehicleQrCodeService
{
    private const LOGO_WIDTH_RATIO = 0.16;

    public function svg(Vehicle $vehicle, ?string $url = null, int $size = 320): string
    {
        return Builder::create()
            ->writer(new SvgWriter)
            ->writerOptions([SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true])
            ->data($url ?? $this->vehicleUrl($vehicle))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size($size)
            ->margin(12)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->logoPath($this->logoPath())
            ->logoResizeToWidth($this->logoWidth($size))
            ->logoPunchoutBackground(true)
            ->validateResult(false)
            ->build()
            ->getString();
    }

    public function dataUri(Vehicle $vehicle, ?string $url = null, int $size = 320): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->svg($vehicle, $url, $size));
    }

    public function pngDataUri(Vehicle $vehicle, ?string $url = null, int $size = 320): string
    {
        return 'data:image/png;base64,'.base64_encode($this->png($vehicle, $url, $size));
    }

    public function png(Vehicle $vehicle, ?string $url = null, int $size = 320): string
    {
        return Builder::create()
            ->writer(new PngWriter)
            ->data($url ?? $this->vehicleUrl($vehicle))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size($size)
            ->margin(12)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->logoPath($this->logoPath())
            ->logoResizeToWidth($this->logoWidth($size))
            ->logoPunchoutBackground(true)
            ->validateResult(false)
            ->build()
            ->getString();
    }

    public function vehicleUrl(Vehicle $vehicle): string
    {
        return rtrim((string) config('app.url'), '/')
            .route('passenger.rating.entry', ['vehicleToken' => $vehicle->qr_token], absolute: false);
    }

    private function logoPath(): string
    {
        return public_path('images/bds/bds-logo.png');
    }

    private function logoWidth(int $qrSize): int
    {
        return max(32, (int) round($qrSize * self::LOGO_WIDTH_RATIO));
    }
}
