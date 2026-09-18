<?php

declare(strict_types=1);

namespace App\View;

final class Renderer
{
    /**
     * @param array<string, mixed> $globals Variables available in every template (e.g. the
     *                                      organizer's contact details); per-render data wins.
     */
    public function __construct(
        private readonly string $templatesDir,
        private readonly array $globals = [],
    ) {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        return $this->renderFile($this->templatesDir . '/' . $template . '.php', $data + $this->globals);
    }

    /**
     * Renders a template and wraps the result as $content inside templates/layout.php.
     *
     * @param array<string, mixed> $data
     * @param bool $fullWidth Skip the centered column so the page can use full-bleed sections (home hero).
     */
    public function renderPage(
        string $template,
        array $data = [],
        string $title = 'Petycje Radzymin',
        ?string $description = null,
        bool $fullWidth = false,
    ): string {
        $content = $this->render($template, $data);

        return $this->render('layout', [
            'content' => $content,
            'title' => $title,
            'description' => $description,
            'fullWidth' => $fullWidth,
        ]);
    }

    /** @param array<string, mixed> $data */
    private function renderFile(string $file, array $data): string
    {
        $render = function (string $__file, array $__data): string {
            // Includes another template (e.g. '_share'), inheriting the current variables.
            $partial = fn (string $name, array $data = []): string => $this->render($name, $data + $__data);
            extract($__data, EXTR_SKIP);
            ob_start();
            require $__file;

            return (string) ob_get_clean();
        };

        return $render($file, $data);
    }
}
