<?php

declare(strict_types=1);

namespace App\Tests\View;

use App\Domain\Petition;
use App\View\Renderer;
use PHPUnit\Framework\TestCase;

final class PrintSheetTest extends TestCase
{
    private function render(Petition $petition, int $rows = 25): string
    {
        $renderer = new Renderer(dirname(__DIR__, 2) . '/templates', [
            'organizer' => [
                'name' => 'Jan Kowalski',
                'address' => 'ul. Testowa 1, Radzymin',
                'contactEmail' => 'jan@example.com',
                'phone' => null,
                'contactFormEndpoint' => null,
            ],
        ]);

        return $renderer->render('print/lista', ['petition' => $petition, 'rows' => $rows]);
    }

    private function petition(string $title = 'Petycja o chodnik'): Petition
    {
        return new Petition(slug: 'chodnik', title: $title, lead: 'Krótki opis.', bodyHtml: '', createdAt: '2026-01-01');
    }

    public function testSheetIsPlainHtmlWithTitleNoticeAndNumberedRows(): void
    {
        $html = $this->render($this->petition());

        self::assertStringStartsWith('<!doctype html>', $html);
        self::assertStringContainsString('<title>Lista podpisów — Petycja o chodnik</title>', $html);
        self::assertStringContainsString('<h1>Petycja o chodnik</h1>', $html);
        self::assertStringContainsString('Jan Kowalski', $html);
        self::assertStringContainsString('jan@example.com', $html);
        self::assertSame(25, substr_count($html, '<td class="lp">'));
        self::assertStringContainsString('<td class="lp">25</td>', $html);
        self::assertStringNotContainsString('<td class="lp">26</td>', $html);
        self::assertMatchesRegularExpression('#/print\.css#', $html);
        self::assertStringContainsString('href="/petycja/chodnik"', $html);
    }

    public function testNumberOfRowsIsConfigurable(): void
    {
        self::assertSame(3, substr_count($this->render($this->petition(), 3), '<td class="lp">'));
    }

    public function testPetitionTextIsEscaped(): void
    {
        $html = $this->render($this->petition('<script>alert(1)</script>'));

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }
}
