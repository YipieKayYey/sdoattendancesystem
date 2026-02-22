<?php

namespace App\Filament\Attendee\Widgets;

use Filament\Widgets\Widget;
use Milon\Barcode\DNS2D;

class UniversalQRWidget extends Widget
{
    protected static string $view = 'filament.attendee.widgets.universal-qr-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 0;

    public function getQrCodeHtml(): string
    {
        $hash = auth()->user()->attendeeProfile?->universal_qr_hash;
        if (!$hash) {
            return '';
        }
        $dns2d = new DNS2D();
        return $dns2d->getBarcodeHTML($hash, 'QRCODE', 8, 8);
    }

    public function getUniversalQrHash(): ?string
    {
        return auth()->user()->attendeeProfile?->universal_qr_hash;
    }
}
