<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Repository\Action\PermissionRepository;
use GibsonOS\Core\Repository\ActionRepository;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\TaskRepository;
use JsonException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class ModuleService
{
    private string $vendorPath;

    /**
     * @deprecated
     */
    private string $oldPath;

    public function __construct(
        private ModuleRepository $moduleRepository,
        private TaskRepository $taskRepository,
        private ActionRepository $actionRepository,
        private PermissionRepository $permissionRepository,
        private DirService $dirService,
        private ReflectionManager $reflectionManager,
        private ModelManager $modelManager,
        private LoggerInterface $logger
    ) {
        $this->vendorPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;

        $this->oldPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'includes' . DIRECTORY_SEPARATOR .
            'module' . DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;
    }

    /**
     * @throws GetError
     * @throws SaveError
     * @throws JsonException
     */
    public function scan(): void
    {
        try {
            $result = $this->scanModules();
            $oldResult = $this->scanOldModules();

            $this->actionRepository->deleteByIdsNot(array_merge($result['actionIds'], $oldResult['actionIds']));
            $this->taskRepository->deleteByIdsNot(array_merge($result['taskIds'], $oldResult['taskIds']));
            $this->moduleRepository->deleteByIdsNot(array_merge($result['moduleIds'], $oldResult['moduleIds']));
        } catch (ReflectionException $e) {
            throw new GetError($e->getMessage());
        }
    }

    /**
     * @throws ReflectionException
     * @throws SaveError
     * @throws JsonException
     * @throws GetError
     *
     * @return array{moduleIds: array<int>, taskIds: array<int>, actionIds: array<int>}
     */
    private function scanModules(): array
    {
        $this->logger->info('Scan modules...');

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
                $this->modelManager->save($module);
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
     * @throws ReflectionException
     * @throws SaveError
     * @throws JsonException
     * @throws GetError
     *
     * @return array{taskIds: array<int>, actionIds: array<int>}
     */
    private function scanTasks(Module $module, string $path): array
    {
        $this->logger->info(sprintf('Scan tasks for module %s...', $module->getName()));
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
            $classname = ucfirst(str_replace('.php', '', str_replace(
                DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR,
                '\\Controller\\',
                $classname
            )));
            /** @var class-string $fqClassname */
            $fqClassname = 'GibsonOS\\Module\\' . $classname;

            try {
                $reflectionClass = $this->reflectionManager->getReflectionClass($fqClassname);
            } catch (ReflectionException) {
                /** @var class-string $fqClassname */
                $fqClassname = 'GibsonOS\\' . $classname;
                $reflectionClass = $this->reflectionManager->getReflectionClass($fqClassname);
            }

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
                $this->modelManager->save($task);
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
     * @throws ReflectionException
     * @throws SaveError
     * @throws JsonException
     *
     * @return int[]
     */
    private function scanActions(Module $module, Task $task, ReflectionClass $reflectionClass): array
    {
        $this->logger->info(sprintf('Scan actions for task %s in module %s...', $task->getName(), $module->getName()));
        $actionIds = [];

        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (mb_strpos($reflectionMethod->getName(), '__') === 0) {
                continue;
            }

            try {
                $action = $this->actionRepository->getByNameAndTaskId($reflectionMethod->getName(), $task->getId() ?? 0);
            } catch (SelectError) {
                $this->logger->info(sprintf('New action %s', $reflectionMethod->getName()));
                $action = (new Action())
                    ->setName($reflectionMethod->getName())
                    ->setModule($module)
                    ->setTask($task)
                ;
                $this->modelManager->save($action);
            }

            $actionIds[] = $action->getId() ?? 0;

            $this->permissionRepository->deleteByAction($action->getName());

            foreach ($this->reflectionManager->getAttributes($reflectionMethod, CheckPermission::class) as $checkPermission) {
                $this->modelManager->save(
                    (new Action\Permission())
                        ->setAction($action)
                        ->setPermission($checkPermission->getPermission())
                );

                foreach ($checkPermission->getPermissionsByRequestValues() as $permission) {
                    $this->modelManager->save(
                        (new Action\Permission())
                            ->setAction($action)
                            ->setPermission($permission)
                    );
                }
            }
        }

        return $actionIds;
    }

    /**
     * @throws GetError
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     *
     * @return array{moduleIds: array<int>, taskIds: array<int>, actionIds: array<int>}
     *
     * @deprecated
     */
    private function scanOldModules(): array
    {
        $moduleIds = [];
        $taskIds = [];
        $actionIds = [];

        foreach ($this->dirService->getFiles($this->oldPath) as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $pos = mb_strrpos($dir, '/') ?: -1;
            $moduleName = mb_substr($dir, $pos + 1);

            try {
                $module = $this->moduleRepository->getByName($moduleName);
            } catch (SelectError) {
                $module = (new Module())->setName($moduleName);
                $this->modelManager->save($module);
            }

            $moduleIds[] = $module->getId() ?? 0;
            $result = $this->scanOldTasks($module, $dir);
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
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     *
     * @return array{taskIds: array<int>, actionIds: array<int>}
     *
     * @deprecated
     */
    private function scanOldTasks(Module $module, string $path): array
    {
        $taskIds = [];
        $actionIds = [];

        foreach ($this->dirService->getFiles($path, '*.php') as $filename) {
            $pos = mb_strrpos($filename, '/') ?: -1;
            $taskName = mb_substr($filename, $pos + 1);
            $pos = mb_strpos($taskName, '.');
            $taskName = strtolower(mb_substr($taskName, 0, $pos ?: null));
            $taskName = str_replace('controller', '', $taskName);

            try {
                $task = $this->taskRepository->getByNameAndModuleId($taskName, $module->getId() ?? 0);
            } catch (SelectError) {
                $task = (new Task())
                    ->setName($taskName)
                    ->setModule($module)
                ;
                $this->modelManager->save($task);
            }

            $taskIds[] = $task->getId() ?? 0;
            $actionIds = array_merge($this->scanOldActions($module, $task, $filename), $actionIds);
        }

        return [
            'taskIds' => $taskIds,
            'actionIds' => $actionIds,
        ];
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     *
     * @return int[]
     *
     * @deprecated
     */
    private function scanOldActions(Module $module, Task $task, string $filename): array
    {
        $actionIds = [];
        $file = file_get_contents($filename) ?: '';
        preg_match_all('/\spublic function ([^_][^\(]+).../si', $file, $actions);

        foreach ($actions[1] as $index => $actionName) {
            try {
                $action = $this->actionRepository->getByNameAndTaskId($actionName, $task->getId() ?? 0);
            } catch (SelectError) {
                $action = (new Action())
                    ->setName($actionName)
                    ->setTask($task)
                    ->setModule($module)
                ;
                $this->modelManager->save($action);
            }

            $actionIds[] = $action->getId() ?? 0;
            $start = (mb_strpos($file, $actions[0][$index]) ?: 0) + mb_strlen($actions[0][$index]);

            if (mb_strpos(mb_substr($file, $start), ' function ')) {
                $length = mb_strpos(mb_substr($file, $start), ' function ') ?: null;
            } else {
                $length = null;
            }

            $substr = mb_substr($file, $start, $length);
            preg_match_all('/\$this-\>checkPermission\((.+?)\)/si', $substr, $permissions);
            $this->permissionRepository->deleteByAction($action->getName());

            foreach ($permissions[1] as $permissionString) {
                $permission = null;
                eval(
                    'use GibsonOS\Core\Model\Permission;' .
                    '$permission = ' . $permissionString . ';'
                );

                if ($permission === null) {
                    continue;
                }

                (new Action\Permission())
                    ->setActionId($action->getId())
                    ->setPermission($permission)
                ;
            }
        }

        return $actionIds;
    }
}
