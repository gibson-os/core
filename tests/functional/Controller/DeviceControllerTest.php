<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Core\Controller;

use GibsonOS\Core\Controller\DeviceController;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\DevicePush;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Model\User\Device;
use GibsonOS\Core\Repository\DevicePushRepository;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Test\Functional\Core\FunctionalTest;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DeviceControllerTest extends FunctionalTest
{
    use ProphecyTrait;

    private DeviceController $deviceController;

    private RequestService|ObjectProphecy $requestService;

    protected function _before(): void
    {
        parent::_before();

        $this->requestService = $this->prophesize(RequestService::class);
        $this->serviceManager->setService(RequestService::class, $this->requestService->reveal());

        $this->deviceController = $this->serviceManager->get(DeviceController::class);
    }

    public function testAddPush(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $device = (new Device())
            ->setId('42')
            ->setToken('galaxy')
            ->setModel('marvin')
            ->setUser($this->addUser())
        ;
        $modelManager->saveWithoutChildren($device);
        $module = (new Module())->setName('arthur');
        $modelManager->saveWithoutChildren($module);
        $task = (new Task())
            ->setName('dent')
            ->setModule($module)
        ;
        $modelManager->saveWithoutChildren($task);
        $action = (new Action())
            ->setName('ford')
            ->setTask($task)
            ->setModule($module)
        ;
        $modelManager->saveWithoutChildren($action);
        $this->requestService->getHeader('X-Device-Token')
            ->shouldBeCalledOnce()
            ->willReturn('galaxy')
        ;
        $deviceRepository = $this->serviceManager->get(DeviceRepository::class);

        $this->checkSuccessResponse(
            $this->deviceController->addPush(
                $modelManager,
                $deviceRepository,
                $module,
                $task,
                $action,
                'prefect',
            )
        );

        $devicePushRepository = $this->serviceManager->get(DevicePushRepository::class);
        $devicePush = $devicePushRepository->getByDevice(
            $device,
            $module->getName(),
            $task->getName(),
            $action->getName(),
            'prefect',
        );

        $this->assertEquals($device->getId(), $devicePush->getDeviceId());
        $this->assertEquals($module->getName(), $devicePush->getModule());
        $this->assertEquals($task->getName(), $devicePush->getTask());
        $this->assertEquals($action->getName(), $devicePush->getAction());
        $this->assertEquals('prefect', $devicePush->getForeignId());
    }

    public function testRemovePush(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $deviceRepository = $this->serviceManager->get(DeviceRepository::class);
        $devicePushRepository = $this->serviceManager->get(DevicePushRepository::class);
        $this->requestService->getHeader('X-Device-Token')
            ->shouldBeCalledOnce()
            ->willReturn('galaxy')
        ;

        $device = (new Device())
            ->setId('42')
            ->setToken('galaxy')
            ->setModel('marvin')
            ->setUser($this->addUser())
        ;
        $modelManager->saveWithoutChildren($device);
        $devicePush = (new DevicePush())
            ->setDevice($device)
            ->setModule('arthur')
            ->setTask('dent')
            ->setAction('ford')
            ->setForeignId('prefect')
        ;
        $modelManager->saveWithoutChildren($devicePush);

        $devicePushRepository->getByDevice($device, 'arthur', 'dent', 'ford', 'prefect');

        $this->checkSuccessResponse(
            $this->deviceController->removePush(
                $modelManager,
                $deviceRepository,
                $devicePushRepository,
                'arthur',
                'dent',
                'ford',
                'prefect',
            )
        );

        $this->expectException(SelectError::class);
        $devicePushRepository->getByDevice($device, 'arthur', 'dent', 'ford', 'prefect');
    }

    public function testRemoveNotExistingPush(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $deviceRepository = $this->serviceManager->get(DeviceRepository::class);
        $devicePushRepository = $this->serviceManager->get(DevicePushRepository::class);
        $this->requestService->getHeader('X-Device-Token')
            ->shouldBeCalledOnce()
            ->willReturn('galaxy')
        ;

        $device = (new Device())
            ->setId('42')
            ->setToken('galaxy')
            ->setModel('marvin')
            ->setUser($this->addUser())
        ;
        $modelManager->saveWithoutChildren($device);

        $this->checkSuccessResponse(
            $this->deviceController->removePush(
                $modelManager,
                $deviceRepository,
                $devicePushRepository,
                'arthur',
                'dent',
                'ford',
                'prefect',
            )
        );
    }

    public function testUpdateToken(): void
    {
        $modelManager = $this->serviceManager->get(ModelManager::class);
        $deviceRepository = $this->serviceManager->get(DeviceRepository::class);
        $this->requestService->getHeader('X-Device-Token')
            ->shouldBeCalledOnce()
            ->willReturn('galaxy')
        ;

        $device = (new Device())
            ->setId('42')
            ->setToken('galaxy')
            ->setModel('marvin')
            ->setUser($this->addUser())
        ;
        $modelManager->saveWithoutChildren($device);

        $this->assertNull($deviceRepository->getById('42')->getFcmToken());

        $this->checkSuccessResponse(
            $this->deviceController->updateToken(
                $modelManager,
                $deviceRepository,
                'zaphod',
            )
        );

        $this->assertEquals('zaphod', $deviceRepository->getById('42')->getFcmToken());
    }
}
