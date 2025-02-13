<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use DateTimeZone;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Attribute\GetStore;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Model\Weather\Location;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\WeatherStore;

class WeatherController extends AbstractController
{
    public function getLocations(): AjaxResponse
    {
        return $this->returnSuccess();
    }

    #[CheckPermission([Permission::READ])]
    public function getWeather(
        #[GetStore]
        WeatherStore $weatherStore,
        DateTimeService $dateTimeService,
        #[GetModel(['id' => 'locationId'])]
        Location $location,
    ): AjaxResponse {
        $weatherStore
            ->setLocationId($location->getId() ?? 0)
            ->setDate($dateTimeService->get('now', new DateTimeZone($location->getTimezone())))
        ;

        return $weatherStore->getAjaxResponse();
    }
}
