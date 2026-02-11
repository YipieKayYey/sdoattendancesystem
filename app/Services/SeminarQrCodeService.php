<?php

namespace App\Services;

use App\Models\Seminar;
use Milon\Barcode\DNS2D;

class SeminarQrCodeService
{
    public function generatePng(Seminar $seminar, int $size = 300): string
    {
        $url = $seminar->registration_url;
        $dns2d = new DNS2D();
        $base64 = $dns2d->getBarcodePNG($url, 'QRCODE,H', 10, 10, [0, 0, 0]);

        if ($base64 === false) {
            throw new \RuntimeException('Failed to generate QR code');
        }

        $png = base64_decode($base64, true);
        $img = @imagecreatefromstring($png);

        if ($img === false) {
            return (string) $png;
        }

        return $this->flattenToWhiteBackground($img);
    }

    /** Flatten image onto opaque white background for better scannability. */
    private function flattenToWhiteBackground(\GdImage $img): string
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $bg = imagecreatetruecolor($w, $h);

        if (!$bg) {
            ob_start();
            imagepng($img);
            $out = ob_get_clean();
            imagedestroy($img);
            return $out;
        }

        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefill($bg, 0, 0, $white);
        imagealphablending($bg, true);
        imagecopy($bg, $img, 0, 0, 0, 0, $w, $h);
        imagedestroy($img);

        ob_start();
        imagepng($bg, null, 6);
        $out = ob_get_clean();
        imagedestroy($bg);

        return $out;
    }
}
