<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\Drive\StatRepository;
use GibsonOS\Core\Repository\DriveRepository;
use GibsonOS\Core\Repository\SmartAttributeRepository;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Store\DriveStore;

class DriveController extends AbstractController
{
    /**
     * @throws SelectError
     */
    #[CheckPermission([Permission::READ])]
    public function get(
        DriveRepository $driveRepository,
        SmartAttributeRepository $smartAttributeRepository,
        StatRepository $statRepository,
    ): AjaxResponse {
        $range = $statRepository->getTimeRange();

        return new AjaxResponse([
            'success' => true,
            'failure' => false,
            'data' => $driveRepository->getDrivesWithAttributes(),
            'attributes' => $smartAttributeRepository->getAll(),
            'from' => $range['min'],
            'to' => $range['max'],
        ]);
    }

    /**
     * @throws SelectError
     */
    #[CheckPermission([Permission::READ])]
    public function getChart(
        DriveStore $driveStore,
        DateTimeService $dateTimeService,
        string $from = null,
        string $fromTime = null,
        string $to = null,
        string $toTime = null,
        int $attributeId = 194,
    ): AjaxResponse {
        $driveStore
            ->setAttributeId($attributeId)
            ->setFrom($from === null ? null : $dateTimeService->get($from))
            ->setFromTime($dateTimeService->get($fromTime ?? 'now'))
            ->setTo($to === null ? null : $dateTimeService->get($to))
            ->setToTime($dateTimeService->get($toTime ?? 'now'))
        ;

        return $this->returnSuccess($driveStore->getList());
    }
}
