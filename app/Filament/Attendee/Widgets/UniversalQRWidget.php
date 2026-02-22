<?php

namespace App\Filament\Attendee\Widgets;

use App\Filament\Attendee\Pages\EditProfile;
use Filament\Widgets\Widget;
use Milon\Barcode\DNS2D;

class UniversalQRWidget extends Widget
{
    protected static string $view = 'filament.attendee.widgets.universal-qr-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 0;

    public function getProfile()
    {
        return auth()->user()->attendeeProfile;
    }

    public function getQrCodeHtml(): string
    {
        $hash = $this->getUniversalQrHash();
        if (!$hash) {
            return '';
        }
        $dns2d = new DNS2D();
        return $dns2d->getBarcodeHTML($hash, 'QRCODE', 8, 8);
    }

    public function getUniversalQrHash(): ?string
    {
        return $this->getProfile()?->universal_qr_hash;
    }

    public function getEditProfileUrl(): string
    {
        return EditProfile::getUrl();
    }
}
