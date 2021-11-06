<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Repository\Action\PermissionRepository;
use GibsonOS\Core\Repository\ActionRepository;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\TaskRepository;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class ModuleService
{
    private string $vendorPath;

    public function __construct(
        private ModuleRepository $moduleRepository,
        private TaskRepository $taskRepository,
        private ActionRepository $actionRepository,
        private PermissionRepository $permissionRepository,
        private DirService $dirService
    ) {
        $this->vendorPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;
    }

    /**
     * @throws GetError
     * @throws ReflectionException
     * @throws SaveError
     */
    public function scan(): void
    {
        $result = $this->scanModules();

        $this->actionRepository->deleteByIdsNot($result['actionIds']);
        $this->taskRepository->deleteByIdsNot($result['taskIds']);
        $this->actionRepository->deleteByIdsNot($result['actionIds']);

        // @todo old shit einbauen
    }

    /**
     * @throws GetError
     * @throws ReflectionException
     * @throws SaveError
     *
     * @return array{moduleIds: array<int>, taskIds: array<int>, actionIds: array<int>}
     */
    private function scanModules(): array
    {
        $moduleIds = [];
        $taskIds = [];
        $actionIds = [];

        foreach ($this->dirService->getFiles($this->vendorPath) as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $pos = mb_strrpos($dir, '/') ?: -1;
            $moduleName = strtolower(mb_substr($dir, $pos + 1));

            try {
                $module = $this->moduleRepository->getByName($moduleName);
            } catch (SelectError) {
                $module = (new Module())->setName($moduleName);
                $module->save();
            }

            $moduleIds[] = $module->getId() ?? 0;
            $result = $this->scanTasks(
                $module,
                $dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR
            );
            $taskIds = array_merge($taskIds, $result['taskIds']);
            $actionIds = array_merge($actionIds, $result['actionIds']);
        }

        return [
            'moduleIds' => $moduleIds,
            'taskIds' => $taskIds,
            'actionIds' => $actionIds,
        ];
    }

    /**
     * @throws GetError
     * @throws ReflectionException
     * @throws SaveError
     *
     * @return array{taskIds: array<int>, actionIds: array<int>}
     */
    private function scanTasks(Module $module, string $path): array
    {
        $taskIds = [];
        $actionIds = [];

        foreach ($this->dirService->getFiles($path, '*Controller.php') as $controller) {
            if (!is_file($controller)) {
                continue;
            }

            $pos = mb_strrpos($controller, '/') ?: -1;
            $taskName = mb_substr($controller, $pos + 1);
            $pos = mb_strpos($taskName, '.');
            $taskName = strtolower(mb_substr($taskName, 0, $pos ?: null));
            $taskName = str_replace('controller', '', $taskName);
            $classname = str_replace($this->vendorPath, '', $controller);
            $classname = str_replace(
                DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR,
                '\\Controller\\',
                $classname
            );
            /** @var class-string $classname */
            $classname = 'GibsonOS\\' . ucfirst(str_replace('.php', '', $classname));
            $reflectionClass = new ReflectionClass($classname);

            if (
                $reflectionClass->isAbstract() ||
                $reflectionClass->isInterface() ||
                $reflectionClass->isTrait()
            ) {
                continue;
            }

            try {
                $task = $this->taskRepository->getByNameAndModuleId($taskName, $module->getId() ?? 0);
            } catch (SelectError) {
                $task = (new Task())
                    ->setName($taskName)
                    ->setModule($module)
                ;
                $task->save();
            }

            $taskIds[] = $task->getId() ?? 0;
            $actionIds = array_merge($this->scanActions($module, $task, $reflectionClass), $actionIds);
        }

        return [
            'taskIds' => $taskIds,
            'actionIds' => $actionIds,
        ];
    }

    /**
     * @throws SaveError
     *
     * @return int[]
     */
    private function scanActions(Module $module, Task $task, ReflectionClass $reflectionClass): array
    {
        $actionIds = [];

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            try {
                $action = $this->actionRepository->getByNameAndTaskId($method->getName(), $task->getId() ?? 0);
            } catch (SelectError) {
                $action = (new Action())
                    ->setName($method->getName())
                    ->setModule($module)
                    ->setTask($task)
                ;
            }

            $action->save();
            $actionIds[] = $action->getId() ?? 0;

            $this->permissionRepository->deleteByAction($action->getName());

            foreach ($method->getAttributes(CheckPermission::class) as $attribute) {
                /** @var CheckPermission $checkPermission */
                $checkPermission = $attribute->newInstance();

                (new Action\Permission())
                    ->setActionId($action->getId() ?? 0)
                    ->setPermission($checkPermission->getPermission())
                    ->save()
                ;

                foreach ($checkPermission->getPermissionsByRequestValues() as $permission) {
                    (new Action\Permission())
                        ->setActionId($action->getId() ?? 0)
                        ->setPermission($permission)
                        ->save()
                    ;
                }
            }
        }

        return $actionIds;
    }
}
