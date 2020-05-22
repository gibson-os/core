<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Service\Response\AjaxResponse;

class IndexController extends AbstractController
{
    public function index(): AjaxResponse
    {
        return $this->returnSuccess();
    }
}
