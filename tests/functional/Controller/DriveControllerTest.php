<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Core\Controller;

use GibsonOS\Core\Controller\DriveController;
use GibsonOS\Core\Install\Data\SmartAttributeData;
use GibsonOS\Core\Repository\Drive\StatRepository;
use GibsonOS\Core\Repository\DriveRepository;
use GibsonOS\Core\Repository\SmartAttributeRepository;
use GibsonOS\Test\Functional\Core\FunctionalTest;

class DriveControllerTest extends FunctionalTest
{
    private DriveController $driveController;

    public function _before(): void
    {
        parent::_before();

        $this->driveController = $this->serviceManager->get(DriveController::class);
    }

    public function testIndex(): void
    {
        $driveRepository = $this->serviceManager->get(DriveRepository::class);
        $smartAttributeRepository = $this->serviceManager->get(SmartAttributeRepository::class);
        $statRepository = $this->serviceManager->get(StatRepository::class);

        $response = $this->driveController->index($driveRepository, $smartAttributeRepository, $statRepository);

        $this->checkAjaxResponse($response, []);

        $body = json_decode($response->getBody(), true);

        $this->assertEquals([], $body['attributes']);
        $this->assertEquals([], $body['attributes']);

        $this->serviceManager->get(SmartAttributeData::class)->install('core');
    }
}
