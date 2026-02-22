<?php

namespace App\Filament\Attendee\Pages;

use Filament\Pages\Page;
use Milon\Barcode\DNS2D;

class UniversalQR extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'My QR Code';

    protected static ?string $title = 'Universal QR Code';

    protected static string $view = 'filament.attendee.pages.universal-qr';

    public function getUniversalQrHash(): ?string
    {
        return auth()->user()->attendeeProfile?->universal_qr_hash;
    }

    public function getQrCodeHtml(): string
    {
        $hash = $this->getUniversalQrHash();
        if (!$hash) {
            return '';
        }
        $dns2d = new DNS2D();
        return $dns2d->getBarcodeHTML($hash, 'QRCODE', 12, 12);
    }
}
