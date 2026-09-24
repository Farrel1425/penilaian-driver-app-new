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
        $svg = Builder::create()
            ->writer(new SvgWriter)
            ->writerOptions([SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true])
            ->data($url ?? $this->vehicleUrl($vehicle))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size($size)
            ->margin(12)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->validateResult(false)
            ->build()
            ->getString();

        return $this->embedSvgLogo($svg, $size);
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
        return public_path('images/bds/bds-logo-qr.png');
    }

    private function logoWidth(int $qrSize): int
    {
        return max(32, (int) round($qrSize * self::LOGO_WIDTH_RATIO));
    }

    private function embedSvgLogo(string $svg, int $qrSize): string
    {
        $logo = file_get_contents($this->logoPath());

        if ($logo === false) {
            return $svg;
        }

        $logoSize = $this->logoWidth($qrSize);
        $padding = max(3, (int) round($logoSize * 0.08));
        $backgroundSize = $logoSize + ($padding * 2);
        $canvasSize = $qrSize;

        if (preg_match('/viewBox="0 0 ([0-9.]+) ([0-9.]+)"/', $svg, $matches) === 1) {
            $canvasSize = (float) $matches[1];
        }

        $logoPosition = ($canvasSize - $logoSize) / 2;
        $backgroundPosition = ($canvasSize - $backgroundSize) / 2;
        $logoMarkup = sprintf(
            '<rect x="%1$s" y="%1$s" width="%2$d" height="%2$d" rx="%3$d" fill="#fff"/><image x="%4$s" y="%4$s" width="%5$d" height="%5$d" preserveAspectRatio="xMidYMid meet" href="data:image/png;base64,%6$s"/>',
            $backgroundPosition,
            $backgroundSize,
            $padding * 2,
            $logoPosition,
            $logoSize,
            base64_encode($logo),
        );

        return str_replace('</svg>', $logoMarkup.'</svg>', $svg);
    }
}
