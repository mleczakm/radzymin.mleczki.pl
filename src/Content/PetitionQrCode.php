<?php

declare(strict_types=1);

namespace App\Content;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;

final class PetitionQrCode
{
    public static function svg(string $url, int $size = 640): string
    {
        $qr = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: $size,
            margin: 28,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255),
        );

        return (new SvgWriter())->write($qr)->getString();
    }

    public static function dataUri(string $url): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(self::svg($url));
    }
}
