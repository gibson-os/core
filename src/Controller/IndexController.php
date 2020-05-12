<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Exception\ControllerError;
use GibsonOS\Core\Service\Response\Response;

class IndexController extends AbstractController
{
    /**
     * @throws ControllerError
     */
    public function index(): Response
    {
        return $this->renderTemplate('@core/base.html.twig');
    }
}
