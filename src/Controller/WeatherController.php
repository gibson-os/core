<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use DateTimeZone;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\Weather\LocationRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\WeatherStore;

class WeatherController extends AbstractController
{
    public function locations(): AjaxResponse
    {
        return $this->returnSuccess();
    }

    /**
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function weather(
        LocationRepository $locationRepository,
        WeatherStore $weatherStore,
        DateTimeService $dateTimeService,
        int $locationId
    ): AjaxResponse {
        $location = $locationRepository->getById($locationId);
        $weatherStore
            ->setLocationId($locationId)
            ->setDate($dateTimeService->get('now', new DateTimeZone($location->getTimezone())))
        ;

        return $this->returnSuccess($weatherStore->getList());
    }
}
