<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Repository\ActionRepository;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\TaskRepository;
use ReflectionClass;
use ReflectionMethod;

class ModuleService
{
    private string $vendorPath;

    public function __construct(
        private ModuleRepository $moduleRepository,
        private TaskRepository $taskRepository,
        private ActionRepository $actionRepository,
        private DirService $dirService
    ) {
        $this->vendorPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;
    }

    public function scan(): void
    {
    }

    /**
     * @throws GetError
     * @throws SaveError
     */
    private function scanModules(): void
    {
        foreach ($this->dirService->getFiles($this->vendorPath) as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $pos = mb_strrpos($dir, '/');
            $moduleName = strtolower(mb_substr($dir, $pos + 1));

            try {
                $module = $this->moduleRepository->getByName($moduleName);
            } catch (SelectError) {
                $module = (new Module())->setName($moduleName);
                $module->save();
            }

            $this->scanTasks(
                $module,
                $dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR
            );
        }
    }

    private function scanTasks(Module $module, string $path): void
    {
        foreach ($this->dirService->getFiles($path, '*Controller.php') as $controller) {
            if (!is_file($controller)) {
                continue;
            }

            $pos = mb_strrpos($controller, '/');
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
                $task = $this->taskRepository->getByNameAndModuleId($taskName, $module->getId());
            } catch (SelectError) {
                $task = (new Task())
                    ->setName($taskName)
                    ->setModule($module)
                ;
                $task->save();
            }

            $this->scanActions($module, $task, $reflectionClass);
        }
    }

    /**
     * @throws SaveError
     */
    private function scanActions(Module $module, Task $task, ReflectionClass $reflectionClass): void
    {
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            try {
                $action = $this->actionRepository->getByNameAndTaskId($method->getName(), $task->getId());
            } catch (SelectError) {
                $action = (new Action())
                    ->setName($method->getName())
                    ->setModule($module)
                    ->setTask($task)
                ;
            }

            $action->save();
        }
    }
}
