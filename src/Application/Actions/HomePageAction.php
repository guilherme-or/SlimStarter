<?php

declare(strict_types=1);

namespace App\Application\Actions;
use Psr\Http\Message\ResponseInterface as Response;

class HomePageAction extends Action
{
    private const HOME_PAGE_TEMPLATE = "Home/page.html";

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        return $this->renderTemplate(self::HOME_PAGE_TEMPLATE);
    }
}