<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\Permission as PermissionEnum;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\ActionRepository;
use GibsonOS\Core\Repository\TaskRepository;
use GibsonOS\Core\Repository\User\PermissionRepository;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use JsonException;
use ReflectionException;

class GeneralPermissionData extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManager $serviceManagerService,
        private readonly PermissionRepository $permissionRepository,
        private readonly TaskRepository $taskRepository,
        private readonly ActionRepository $actionRepository,
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws SaveError
     * @throws JsonException
     * @throws ReflectionException
     */
    public function install(string $module): Generator
    {
        $coreModule = $this->moduleRepository->getByName('core');
        $userTask = $this->taskRepository->getByNameAndModuleId('user', $coreModule->getId() ?? 0);
        $middlewareTask = $this->taskRepository->getByNameAndModuleId('middleware', $coreModule->getId() ?? 0);
        $loginAction = $this->actionRepository->getByNameAndTaskId('login', HttpMethod::POST, $userTask->getId() ?? 0);

        try {
            $this->permissionRepository->getByModuleTaskAndAction($coreModule, $userTask, $loginAction);
        } catch (SelectError) {
            $this->modelManager->save(
                (new Permission($this->modelWrapper))
                    ->setModule($coreModule)
                    ->setTask($userTask)
                    ->setAction($loginAction)
                    ->setPermission(PermissionEnum::READ->value),
            );
        }

        try {
            $this->permissionRepository->getByModuleAndTask($coreModule, $middlewareTask);
        } catch (SelectError) {
            $this->modelManager->save(
                (new Permission($this->modelWrapper))
                    ->setModule($coreModule)
                    ->setTask($middlewareTask)
                    ->setPermission(PermissionEnum::READ->value + PermissionEnum::WRITE->value),
            );
        }

        yield new Success('Set general permission for core!');
    }

    public function getPart(): string
    {
        return InstallService::PART_DATA;
    }

    public function getModule(): ?string
    {
        return 'core';
    }

    public function getPriority(): int
    {
        return 0;
    }
}
