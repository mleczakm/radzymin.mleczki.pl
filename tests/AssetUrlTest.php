<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class AssetUrlTest extends TestCase
{
    public function testExistingAssetGetsAVersionFromItsModificationTime(): void
    {
        $url = asset_url('style.css');

        self::assertMatchesRegularExpression('#^/style\.css\?v=\d+$#', $url);
        self::assertSame(
            '?v=' . filemtime(dirname(__DIR__) . '/public/style.css'),
            substr($url, strlen('/style.css')),
        );
    }

    public function testLeadingSlashIsOptional(): void
    {
        self::assertSame(asset_url('style.css'), asset_url('/style.css'));
    }

    public function testMissingAssetKeepsThePlainPath(): void
    {
        self::assertSame('/does-not-exist.js', asset_url('does-not-exist.js'));
    }
}
