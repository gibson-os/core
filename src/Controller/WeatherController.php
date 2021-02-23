<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Service\Response\AjaxResponse;

class WeatherController extends AbstractController
{
    public function locations(): AjaxResponse
    {
    }

    public function weather(int $locationId): AjaxResponse
    {
    }
}
