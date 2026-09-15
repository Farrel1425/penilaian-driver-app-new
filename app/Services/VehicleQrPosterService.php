<?php

namespace App\Services;

use App\Models\Vehicle;
use GdImage;
use RuntimeException;

class VehicleQrPosterService
{
    public const WIDTH = 700;

    public const HEIGHT = 1000;

    private const INFORMATION_TEXT_WIDTH = 160;

    private const INFORMATION_CENTER_X = 349.5;

    private const INFORMATION_CARD_HEIGHT = 36;

    private const QR_CENTER_X = 353;

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

        $qrX = (int) round(self::QR_CENTER_X - (imagesx($qr) / 2));
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

        $fontSize = 13;
        foreach ($entries as [$label, $value]) {
            while ($fontSize > 9 && $this->mixedTextWidth($label, $value, $fontSize) > self::INFORMATION_TEXT_WIDTH) {
                $fontSize--;
            }
        }

        foreach ($entries as $index => [$label, $value]) {
            $y = 794 + ($index * 44);
            $this->centeredMixedText($image, $label, $value, $fontSize, $y, self::INFORMATION_CARD_HEIGHT);
        }
    }

    private function centeredMixedText(GdImage $image, string $label, string $value, int $size, int $top, int $height): void
    {
        $label .= ' ';
        $labelWidth = $this->textWidth($label, $size, $this->regularFont);
        $valueWidth = $this->textWidth($value, $size, $this->boldFont);
        $x = (int) round(self::INFORMATION_CENTER_X - (($labelWidth + $valueWidth) / 2));

        $regularBox = imagettfbbox($size, 0, $this->regularFont, $label);
        $boldBox = imagettfbbox($size, 0, $this->boldFont, $value);
        $minimumY = min($regularBox[1], $regularBox[3], $regularBox[5], $regularBox[7], $boldBox[1], $boldBox[3], $boldBox[5], $boldBox[7]);
        $maximumY = max($regularBox[1], $regularBox[3], $regularBox[5], $regularBox[7], $boldBox[1], $boldBox[3], $boldBox[5], $boldBox[7]);
        $baseline = (int) round($top + ($height / 2) - (($minimumY + $maximumY) / 2));

        imagettftext($image, $size, 0, $x, $baseline, $this->rgb($image, '#0f4d32'), $this->regularFont, $label);
        imagettftext($image, $size, 0, $x + $labelWidth, $baseline, $this->rgb($image, '#098f42'), $this->boldFont, $value);
    }

    private function mixedTextWidth(string $label, string $value, int $size): int
    {
        return $this->textWidth($label.' ', $size, $this->regularFont)
            + $this->textWidth($value, $size, $this->boldFont);
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
