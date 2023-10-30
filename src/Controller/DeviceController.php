<?php
declare(strict_types=1);

namespace GibsonOS\Core\Controller;

use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\DevicePush;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Repository\DevicePushRepository;
use GibsonOS\Core\Repository\User\DeviceRepository;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class DeviceController extends AbstractController
{
    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws RequestError
     * @throws SaveError
     * @throws SelectError
     * @throws ClientException
     * @throws RecordException
     */
    public function postAddPush(
        ModelManager $modelManager,
        ModelWrapper $modelWrapper,
        DeviceRepository $deviceRepository,
        #[GetModel(['name' => 'module'])]
        Module $module,
        #[GetModel(['name' => 'task'])]
        Task $task,
        #[GetModel(['name' => 'action'])]
        Action $action,
        string $foreignId,
    ): AjaxResponse {
        $deviceToken = $this->requestService->getHeader('X-Device-Token');
        $device = $deviceRepository->getByToken($deviceToken);
        $devicePush = (new DevicePush($modelWrapper))
            ->setDevice($device)
            ->setModuleModel($module)
            ->setTaskModel($task)
            ->setActionModel($action)
            ->setForeignId($foreignId)
        ;
        $modelManager->saveWithoutChildren($devicePush);

        return $this->returnSuccess();
    }

    /**
     * @throws ClientException
     * @throws DeleteError
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws RequestError
     * @throws SelectError
     */
    public function deletePush(
        ModelManager $modelManager,
        DeviceRepository $deviceRepository,
        DevicePushRepository $devicePushRepository,
        string $module,
        string $task,
        string $action,
        string $foreignId,
    ): AjaxResponse {
        $deviceToken = $this->requestService->getHeader('X-Device-Token');
        $device = $deviceRepository->getByToken($deviceToken);

        try {
            $devicePush = $devicePushRepository->getByDevice($device, $module, $task, $action, $foreignId);
            $modelManager->delete($devicePush);
        } catch (SelectError) {
        }

        return $this->returnSuccess();
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws RequestError
     * @throws SaveError
     * @throws SelectError
     */
    public function postUpdateToken(
        ModelManager $modelManager,
        DeviceRepository $deviceRepository,
        string $fcmToken,
    ): AjaxResponse {
        $deviceToken = $this->requestService->getHeader('X-Device-Token');
        $modelManager->save(
            $deviceRepository
                ->getByToken($deviceToken)
                ->setFcmToken($fcmToken),
        );

        return $this->returnSuccess();
    }
}
