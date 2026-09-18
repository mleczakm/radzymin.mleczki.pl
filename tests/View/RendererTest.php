<?php

declare(strict_types=1);

namespace App\Tests\View;

use App\View\Renderer;
use PHPUnit\Framework\TestCase;

final class RendererTest extends TestCase
{
    private const TEMPLATES = __DIR__ . '/../fixtures/templates';

    public function testGlobalsAreAvailableInEveryTemplate(): void
    {
        $renderer = new Renderer(self::TEMPLATES, ['site' => 'Radzymin']);

        self::assertSame('Hello Ola from Radzymin', trim($renderer->render('greeting', ['name' => 'Ola'])));
    }

    public function testPerRenderDataWinsOverGlobals(): void
    {
        $renderer = new Renderer(self::TEMPLATES, ['site' => 'Global', 'name' => 'Global']);

        self::assertSame('Hello Local from Override', trim($renderer->render('greeting', ['name' => 'Local', 'site' => 'Override'])));
    }

    public function testOutputIsEscapedByTheTemplateHelper(): void
    {
        $renderer = new Renderer(self::TEMPLATES, ['site' => 'S']);

        self::assertStringContainsString('&lt;b&gt;', $renderer->render('greeting', ['name' => '<b>']));
    }

    public function testPartialInheritsCurrentVariablesAndAcceptsExtraData(): void
    {
        $renderer = new Renderer(self::TEMPLATES, ['site' => 'Radzymin']);

        self::assertSame('[Hello Partial from Radzymin]', trim($renderer->render('with_partial')));
    }

    public function testRenderPageWrapsContentInTheLayout(): void
    {
        $renderer = new Renderer(self::TEMPLATES, ['site' => 'Radzymin']);

        $html = $renderer->renderPage('greeting', ['name' => 'Ola'], 'Tytuł', 'Opis', fullWidth: true);

        self::assertStringContainsString('<title>Tytuł</title>', $html);
        self::assertStringContainsString('<desc>Opis</desc>', $html);
        self::assertStringContainsString('<full>yes</full>', $html);
        self::assertStringContainsString('Hello Ola from Radzymin', $html);
    }

    public function testRenderPageDefaultsToTheCenteredColumn(): void
    {
        $renderer = new Renderer(self::TEMPLATES, ['site' => 'S']);

        self::assertStringContainsString('<full>no</full>', $renderer->renderPage('greeting', ['name' => 'x']));
    }
}
