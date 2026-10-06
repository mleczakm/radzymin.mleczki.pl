<?php

declare(strict_types=1);

namespace App\Tests\View;

use App\Domain\Petition;
use App\View\Renderer;
use PHPUnit\Framework\TestCase;

final class PublicPagesTest extends TestCase
{
    private function renderer(): Renderer
    {
        return new Renderer(dirname(__DIR__, 2) . '/templates', [
            'baseUrl' => 'https://example.com/',
            'organizer' => [
                'name' => 'Test Organizer',
                'address' => 'Radzymin',
                'contactEmail' => 'test@example.com',
                'phone' => null,
                'contactFormEndpoint' => null,
                'turnstileSiteKey' => null,
            ],
        ]);
    }

    private function petition(): Petition
    {
        return new Petition('chodnik', 'Petycja o chodnik', 'Opis inicjatywy.', '', '2026-01-01');
    }

    public function testPublicPageHasCanonicalAndRasterSocialImage(): void
    {
        $html = $this->renderer()->renderPage('error', ['message' => 'Test'], canonicalPath: '/o-mnie');

        self::assertStringContainsString('<link rel="canonical" href="https://example.com/o-mnie">', $html);
        self::assertStringContainsString('content="https://example.com/img/social-card.png"', $html);
        self::assertStringContainsString('name="twitter:card" content="summary_large_image"', $html);
        self::assertStringNotContainsString('noindex', $html);
    }

    public function testTechnicalPageCannotBeIndexedAndHasNoCanonical(): void
    {
        $html = $this->renderer()->renderPage('thank_you', ['petition' => $this->petition()]);

        self::assertStringContainsString('<meta name="robots" content="noindex, follow">', $html);
        self::assertStringNotContainsString('rel="canonical"', $html);
    }

    public function testPetitionErrorsHaveLinkedFieldsAndPreserveEnteredData(): void
    {
        $html = $this->renderer()->render('petition', [
            'petition' => $this->petition(),
            'progressHtml' => '',
            'recentSignaturesHtml' => '',
            'errors' => ['first_name' => 'Podaj imię.', 'email' => 'Podaj e-mail.'],
            'old' => ['first_name' => '<script>', 'email' => 'wrong', 'consent' => '1'],
            'timingToken' => bin2hex(random_bytes(16)),
            'honeypotField' => 'website',
        ]);

        self::assertStringContainsString('href="#first_name"', $html);
        self::assertStringContainsString('aria-describedby="first_name-error"', $html);
        self::assertStringContainsString('id="first_name-error"', $html);
        self::assertStringContainsString('aria-describedby="email-hint email-error"', $html);
        self::assertStringContainsString('value="&lt;script&gt;"', $html);
        self::assertMatchesRegularExpression('/id="consent"[^>]*checked/s', $html);
        self::assertStringNotContainsString('novalidate', $html);
        self::assertLessThan(strpos($html, 'class="help-box"'), strpos($html, 'class="signature-form"'));
    }

    public function testFeaturedPetitionWithoutOptionalCopyStillHasRelevantHero(): void
    {
        $petition = $this->petition();
        $html = $this->renderer()->render('home', [
            'petitions' => [$petition->slug => $petition],
            'featured' => $petition,
            'counts' => [$petition->slug => 0],
            'percents' => [$petition->slug => null],
            'topics' => [],
        ]);

        self::assertStringContainsString('<h1>Petycja o chodnik</h1>', $html);
        self::assertStringContainsString('Opis inicjatywy.', $html);
        self::assertStringNotContainsString('href="#sprawy"', $html);
    }
}
