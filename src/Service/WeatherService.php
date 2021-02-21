<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Dto\Web\Response;

class WeatherService extends AbstractService
{
    private WebService $webService;

    private EnvService $envService;

    public function __construct(WebService $webService, EnvService $envService)
    {
        $this->webService = $webService;
        $this->envService = $envService;
    }

    public function getByCoordinates(float $latitude, float $longitude): Response
    {
        return $this->request('onecall?lat=' . $latitude . '&lon=' . $longitude);
    }

    private function request(string $uri): Response
    {
        return $this->webService->get(
            (new Request(
                'https://' . $this->envService->getString('OPENWEATHERMAP_URL') . $uri .
                '&appid=' . $this->envService->getString('OPENWEATHERMAP_API_KEY') .
                '&units=metric&lang=de'
            ))->setPort(443)
        );
    }
}
