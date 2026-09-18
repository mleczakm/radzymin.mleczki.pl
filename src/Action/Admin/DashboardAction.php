<?php

declare(strict_types=1);

namespace App\Action\Admin;

use App\Http\Responder;
use App\Runtime\WorkerServices;
use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class DashboardAction
{
    public function __construct(private readonly WorkerServices $services, private readonly Renderer $view)
    {
    }

    public function __invoke(Request $request, Response $response): void
    {
        if (!$this->services->adminAuth->check($request, $response)) {
            return;
        }

        $rows = [];
        foreach ($this->services->petitions->all() as $petition) {
            $slug = $petition->slug;
            $rows[] = [
                'petition' => $petition,
                'confirmed' => $this->services->signatures->countConfirmed($slug),
                'pending' => $this->services->signatures->countPending($slug),
            ];
        }

        Responder::html($response, $this->view->renderPage('admin/dashboard', [
            'rows' => $rows,
        ], 'Panel administracyjny'));
    }
}
