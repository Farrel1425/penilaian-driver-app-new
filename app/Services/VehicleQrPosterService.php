<?php

namespace App\Services;

use App\Models\Vehicle;
use GdImage;
use RuntimeException;

class VehicleQrPosterService
{
    public const WIDTH = 700;

    public const HEIGHT = 1000;

    private string $regularFont;

    private string $boldFont;

    public function __construct(private readonly VehicleQrCodeService $qrCode)
    {
        $this->regularFont = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $this->boldFont = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
    }

    public function render(Vehicle $vehicle): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Ekstensi GD diperlukan untuk membuat poster QR kendaraan.');
        }

        $template = public_path('images/qr-vehicle-template.png');
        $canvas = is_file($template) ? imagecreatefrompng($template) : false;

        if (! $canvas || imagesx($canvas) !== self::WIDTH || imagesy($canvas) !== self::HEIGHT) {
            throw new RuntimeException('Template poster QR kendaraan harus berupa PNG berukuran 700x1000 px.');
        }

        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        $this->drawQr($canvas, $vehicle);
        $this->drawVehicleInformation($canvas, $vehicle);

        ob_start();
        imagepng($canvas, null, 9);
        $png = ob_get_clean();
        imagedestroy($canvas);

        if (! is_string($png)) {
            throw new RuntimeException('Poster QR kendaraan gagal dibuat.');
        }

        return $png;
    }

    private function drawQr(GdImage $image, Vehicle $vehicle): void
    {
        $qr = imagecreatefromstring($this->qrCode->png($vehicle, size: 248));
        if (! $qr) {
            throw new RuntimeException('Gambar QR kendaraan gagal dibuat.');
        }

        $qrX = (int) round((self::WIDTH - imagesx($qr)) / 2);
        $qrY = (int) round((296 + 595 - imagesy($qr)) / 2);
        imagecopy($image, $qr, $qrX, $qrY, 0, 0, imagesx($qr), imagesy($qr));
        imagedestroy($qr);
    }

    private function drawVehicleInformation(GdImage $image, Vehicle $vehicle): void
    {
        $entries = [
            ['Plat Nomor:', $vehicle->police_number],
            ['Merk:', trim($vehicle->brand.' '.$vehicle->model)],
            ['Lokasi:', $vehicle->branch?->name ?? '-'],
        ];

        foreach ($entries as $index => [$label, $value]) {
            $y = 794 + ($index * 44);
            $this->centeredMixedText($image, $label, $value, 13, $y + 9, 320);
        }
    }

    private function centeredMixedText(GdImage $image, string $label, string $value, int $size, int $top, int $maxWidth): void
    {
        $space = ' ';
        while ($size > 9) {
            $width = $this->textWidth($label.$space, $size, $this->regularFont) + $this->textWidth($value, $size, $this->boldFont);
            if ($width <= $maxWidth) {
                break;
            }
            $size--;
        }

        $width = $this->textWidth($label.$space, $size, $this->regularFont) + $this->textWidth($value, $size, $this->boldFont);
        $x = (int) round((self::WIDTH - $width) / 2);
        $this->text($image, $label.$space, $size, $x, $top, $this->rgb($image, '#0f4d32'), $this->regularFont);
        $this->text($image, $value, $size, $x + $this->textWidth($label.$space, $size, $this->regularFont), $top, $this->rgb($image, '#098f42'), $this->boldFont);
    }

    private function text(GdImage $image, string $text, int $size, int $x, int $top, int $color, string $font): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        $baseline = $top - min($box[1], $box[3], $box[5], $box[7]);
        imagettftext($image, $size, 0, $x, $baseline, $color, $font, $text);
    }

    private function textWidth(string $text, int $size, string $font): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return max($box[0], $box[2], $box[4], $box[6]) - min($box[0], $box[2], $box[4], $box[6]);
    }

    private function rgb(GdImage $image, string $hex): int
    {
        $hex = ltrim($hex, '#');

        return imagecolorallocate(
            $image,
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        );
    }
}
