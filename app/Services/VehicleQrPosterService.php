<?php

namespace App\Services;

use App\Models\Vehicle;
use GdImage;
use RuntimeException;

class VehicleQrPosterService
{
    public const WIDTH = 700;

    public const HEIGHT = 1000;

    private const RENDERER_VERSION = 3;

    private const QR_SIZE = 248;

    private const QR_CORNER_RADIUS = 20;

    private const QR_CENTER_X = 350;

    private const QR_CENTER_Y = 500;

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

        $template = public_path('images/qr-vehicle-poster.png');

        if (! is_file($template)) {
            throw new RuntimeException('Template poster QR kendaraan tidak ditemukan.');
        }

        $cacheKey = hash('sha256', json_encode([
            'renderer_version' => self::RENDERER_VERSION,
            'template' => hash_file('sha256', $template),
            'app_url' => $this->qrCode->vehicleUrl($vehicle),
            'vehicle_id' => $vehicle->getKey(),
            'qr_token' => $vehicle->qr_token,
            'police_number' => $vehicle->police_number,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'branch' => $vehicle->branch?->name,
        ], JSON_THROW_ON_ERROR));
        $cacheDirectory = storage_path('app/qr-posters');
        $cachePath = $cacheDirectory.'/vehicle-'.$vehicle->getKey().'-'.$cacheKey.'.png';

        if (is_file($cachePath) && ($poster = file_get_contents($cachePath)) !== false) {
            return $poster;
        }

        if (! is_dir($cacheDirectory) && ! mkdir($cacheDirectory, 0775, true) && ! is_dir($cacheDirectory)) {
            throw new RuntimeException('Direktori cache poster QR kendaraan tidak dapat dibuat.');
        }

        $poster = $this->renderUncached($vehicle, $template);
        $temporaryPath = $cachePath.'.'.bin2hex(random_bytes(6)).'.tmp';

        if (file_put_contents($temporaryPath, $poster, LOCK_EX) === false) {
            throw new RuntimeException('Poster QR kendaraan gagal disimpan ke cache.');
        }

        if (! @rename($temporaryPath, $cachePath)) {
            @unlink($temporaryPath);

            if (! is_file($cachePath)) {
                throw new RuntimeException('Poster QR kendaraan gagal dipindahkan ke cache.');
            }
        }

        return $poster;
    }

    private function renderUncached(Vehicle $vehicle, string $template): string
    {
        $canvas = is_file($template) ? imagecreatefrompng($template) : false;

        if (! $canvas || imagesx($canvas) !== self::WIDTH || imagesy($canvas) !== self::HEIGHT) {
            throw new RuntimeException('Template poster QR kendaraan harus berupa PNG berukuran 700x1000 px.');
        }

        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        $this->drawQr($canvas, $vehicle);
        $this->drawVehicleInformation($canvas, $vehicle);

        ob_start();
        imagepng($canvas, null, 6);
        $png = ob_get_clean();
        imagedestroy($canvas);

        if (! is_string($png)) {
            throw new RuntimeException('Poster QR kendaraan gagal dibuat.');
        }

        return $png;
    }

    private function drawQr(GdImage $image, Vehicle $vehicle): void
    {
        $qr = imagecreatefromstring($this->qrCode->png($vehicle, size: self::QR_SIZE));
        if (! $qr) {
            throw new RuntimeException('Gambar QR kendaraan gagal dibuat.');
        }

        $this->roundCorners($qr, self::QR_CORNER_RADIUS);

        $qrX = (int) round(self::QR_CENTER_X - (imagesx($qr) / 2));
        $qrY = (int) round(self::QR_CENTER_Y - (imagesy($qr) / 2));
        imagecopy($image, $qr, $qrX, $qrY, 0, 0, imagesx($qr), imagesy($qr));
        imagedestroy($qr);
    }

    private function roundCorners(GdImage $image, int $radius): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);

        imagealphablending($image, false);
        imagesavealpha($image, true);

        foreach ([[0, 0], [$width - $radius, 0], [0, $height - $radius], [$width - $radius, $height - $radius]] as [$originX, $originY]) {
            $centerX = $originX === 0 ? $radius - 1 : $originX;
            $centerY = $originY === 0 ? $radius - 1 : $originY;

            for ($x = $originX; $x < $originX + $radius; $x++) {
                for ($y = $originY; $y < $originY + $radius; $y++) {
                    if ((($x - $centerX) ** 2) + (($y - $centerY) ** 2) >= $radius ** 2) {
                        imagesetpixel($image, $x, $y, $transparent);
                    }
                }
            }
        }

        imagealphablending($image, true);
    }

    private function drawVehicleInformation(GdImage $image, Vehicle $vehicle): void
    {
        $this->centeredText($image, strtoupper($vehicle->police_number), 17, 350, 658, 44, $this->boldFont, '#063d29', 240);
        $this->drawInformationCard($image, 'MERK / TIPE', trim($vehicle->brand.' '.$vehicle->model), 235, 855, 212, 66);
        $this->drawInformationCard($image, 'UNIT KERJA', $vehicle->branch?->name ?? '-', 465, 855, 212, 66);
    }

    private function drawInformationCard(GdImage $image, string $label, string $value, int $centerX, int $top, int $width, int $height): void
    {
        $this->centeredText($image, $label, 8, $centerX, $top + 8, 16, $this->regularFont, '#64746c', $width - 20);
        $this->centeredText($image, $value, 11, $centerX, $top + 25, 30, $this->boldFont, '#063d29', $width - 20);
    }

    private function centeredText(
        GdImage $image,
        string $text,
        int $size,
        int $centerX,
        int $top,
        int $height,
        string $font,
        string $color,
        int $maxWidth,
    ): void {
        while ($size > 7 && $this->textWidth($text, $size, $font) > $maxWidth) {
            $size--;
        }

        $box = imagettfbbox($size, 0, $font, $text);
        $minimumX = min($box[0], $box[2], $box[4], $box[6]);
        $maximumX = max($box[0], $box[2], $box[4], $box[6]);
        $minimumY = min($box[1], $box[3], $box[5], $box[7]);
        $maximumY = max($box[1], $box[3], $box[5], $box[7]);
        $x = (int) round($centerX - (($maximumX - $minimumX) / 2) - $minimumX);
        $baseline = (int) round($top + ($height / 2) - (($minimumY + $maximumY) / 2));

        imagettftext($image, $size, 0, $x, $baseline, $this->rgb($image, $color), $font, $text);
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
