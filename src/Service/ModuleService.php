<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Enum\HttpMethod;
use GibsonOS\Core\Enum\Permission;
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
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class ModuleService
{
    private string $modulesPath;

    public function __construct(
        private readonly ModuleRepository $moduleRepository,
        private readonly TaskRepository $taskRepository,
        private readonly ActionRepository $actionRepository,
        private readonly PermissionRepository $permissionRepository,
        private readonly DirService $dirService,
        private readonly ReflectionManager $reflectionManager,
        private readonly ModelManager $modelManager,
        private readonly LoggerInterface $logger,
        private readonly ModelWrapper $modelWrapper,
        ?string $modulesPath = null,
    ) {
        $this->modulesPath = $modulesPath ?? realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR,
        ) . DIRECTORY_SEPARATOR;
    }

    /**
     * @throws ClientException
     * @throws GetError
     * @throws JsonException
     * @throws RecordException
     * @throws SaveError
     */
    public function scan(): void
    {
        try {
            $result = $this->scanModules();

            $this->actionRepository->deleteByIdsNot($result['actionIds']);
            $this->taskRepository->deleteByIdsNot($result['taskIds']);
            $this->moduleRepository->deleteByIdsNot($result['moduleIds']);
        } catch (ReflectionException $e) {
            throw new GetError($e->getMessage());
        }
    }

    /**
     * @throws GetError
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     * @throws ClientException
     * @throws RecordException
     *
     * @return array{moduleIds: array<int>, taskIds: array<int>, actionIds: array<int>}
     */
    private function scanModules(): array
    {
        $this->logger->info('Scan modules...');

        $moduleIds = [];
        $taskIds = [];
        $actionIds = [];

        foreach ($this->dirService->getFiles($this->modulesPath) as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $pos = mb_strrpos($dir, DIRECTORY_SEPARATOR) ?: -1;
            $moduleName = $this->transformModuleName(strtolower(mb_substr($dir, $pos + 1)));

            try {
                $module = $this->moduleRepository->getByName($moduleName);
            } catch (SelectError) {
                $module = (new Module($this->modelWrapper))->setName($moduleName);
                $this->modelManager->saveWithoutChildren($module);
            }

            $moduleIds[] = $module->getId() ?? 0;
            $result = $this->scanTasks(
                $module,
                $dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR,
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
            $classname = str_replace($this->modulesPath, '', $controller);
            $classname = ucfirst(str_replace('.php', '', str_replace(
                DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR,
                '\\Controller\\',
                $classname,
            )));
            $classname = $this->transformModuleName($classname);
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
                $reflectionClass->isAbstract()
                || $reflectionClass->isInterface()
                || $reflectionClass->isTrait()
            ) {
                continue;
            }

            try {
                $task = $this->taskRepository->getByNameAndModuleId($taskName, $module->getId() ?? 0);
            } catch (SelectError) {
                $task = (new Task($this->modelWrapper))
                    ->setName($taskName)
                    ->setModule($module)
                ;
                $this->modelManager->saveWithoutChildren($task);
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
            $methodName = $reflectionMethod->getName();

            if (mb_strpos($methodName, '__') === 0) {
                continue;
            }

            if (!preg_match('/([a-z]*)(.*)/', $methodName, $hits)) {
                continue;
            }

            $method = HttpMethod::from(mb_strtoupper($hits[1]));
            $name = lcfirst($hits[2]);

            try {
                $action = $this->actionRepository->getByNameAndTaskId($name, $method, $task->getId() ?? 0);
            } catch (SelectError) {
                $this->logger->info(sprintf('New action %s', $methodName));
                $action = (new Action($this->modelWrapper))
                    ->setName($name)
                    ->setMethod($method)
                    ->setModule($module)
                    ->setTask($task)
                ;
                $this->modelManager->saveWithoutChildren($action);
            }

            $actionIds[] = $action->getId() ?? 0;

            $this->permissionRepository->deleteByAction($action);

            foreach ($this->reflectionManager->getAttributes($reflectionMethod, CheckPermission::class, ReflectionAttribute::IS_INSTANCEOF) as $checkPermission) {
                $this->modelManager->saveWithoutChildren(
                    (new Action\Permission($this->modelWrapper))
                        ->setAction($action)
                        ->setPermission($this->getPermissionSum($checkPermission->getPermissions())),
                );

                foreach ($checkPermission->getPermissionsByRequestValues() as $permissions) {
                    $this->modelManager->saveWithoutChildren(
                        (new Action\Permission($this->modelWrapper))
                            ->setAction($action)
                            ->setPermission($this->getPermissionSum($permissions)),
                    );
                }
            }
        }

        return $actionIds;
    }

    /**
     * @param Permission[] $permissions
     */
    private function getPermissionSum(array $permissions): int
    {
        return array_sum(array_map(
            static fn (Permission $permission): int => $permission->value,
            $permissions,
        ));
    }

    private function transformModuleName(string $moduleName): string
    {
        return preg_replace_callback(
            '/-(\w)/',
            static fn ($matches) => mb_strtoupper($matches[1]),
            $moduleName,
        );
    }
}
