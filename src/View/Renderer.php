<?php

declare(strict_types=1);

namespace App\View;

final class Renderer
{
    public function __construct(private readonly string $templatesDir)
    {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        return $this->renderFile($this->templatesDir . '/' . $template . '.php', $data);
    }

    /** Renders a template and wraps the result as $content inside templates/layout.php. */
    public function renderPage(string $template, array $data = [], string $title = 'Petycje Radzymin'): string
    {
        $content = $this->render($template, $data);

        return $this->render('layout', ['content' => $content, 'title' => $title]);
    }

    /** @param array<string, mixed> $data */
    private function renderFile(string $file, array $data): string
    {
        $render = function (string $__file, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            require $__file;

            return (string) ob_get_clean();
        };

        return $render($file, $data);
    }
}
