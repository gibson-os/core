<?php declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\User\PermissionViewRepository;

class JavascriptService extends AbstractService
{
    /**
     * @var PermissionViewRepository
     */
    private $permissionViewRepository;

    /**
     * @var DirService
     */
    private $dirService;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var PermissionService
     */
    private $permissionService;

    public function __construct(
        PermissionViewRepository $permissionViewRepository,
        DirService $dirService,
        FileService $fileService,
        PermissionService $permissionService
    ) {
        $this->permissionViewRepository = $permissionViewRepository;
        $this->dirService = $dirService;
        $this->fileService = $fileService;
        $this->permissionService = $permissionService;
    }

    /**
     * @throws SelectError
     */
    public function getByUserId(?int $userId, string $module = null): string
    {
        $files = [];
        $filesOrder = [];
        $filesExtends = [];
        $oldData = '';

        foreach ($this->permissionViewRepository->getTaskList($userId, $module) as $task) {
            $dir =
                'js' . DIRECTORY_SEPARATOR .
                'module' . DIRECTORY_SEPARATOR .
                $task->module . DIRECTORY_SEPARATOR .
                $task->task . DIRECTORY_SEPARATOR
            ;

            $classFiles = $this->mergeClassFileContent($dir, $files, $filesOrder, $filesExtends);
            $filesOrder = $classFiles['order'];
            $filesExtends = $classFiles['extends'];
            $files = $classFiles['content'];

            $oldData .= $this->mergeFileContent($dir);
        }

        return $this->getClassFilesContent($files, $filesOrder) . $oldData;
    }

    public function getByUserIdAndTask(?int $userId, string $module, string $task): string
    {
        if ($this->permissionService->isDenied($module, null, null, $userId)) {
            return '';
        }

        $dir =
            'js' . DIRECTORY_SEPARATOR .
            'module' . DIRECTORY_SEPARATOR .
            $module . DIRECTORY_SEPARATOR .
            $task . DIRECTORY_SEPARATOR
        ;

        $classFiles = $this->mergeClassFileContent($dir);

        return
            $this->getClassFilesContent($classFiles['content'], $classFiles['order']) .
            $this->mergeFileContent($dir)
        ;
    }

    private function mergeFileContent(string $dir): string
    {
        $return = '';

        // Muss erstmal für die Kompatibilität erhalten bleiben
        $filename = $this->dirService->removeEndSlash($dir) . '.js';

        if (file_exists($filename)) {
            $return .= "\n/* " . str_replace('js' . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR, '', $filename) . " */\n";
            $return .= file_get_contents($filename);
        }

        if (!is_dir($dir)) {
            return $return;
        }

        return $return;
    }

    private function mergeClassFileContent(
        string $dir,
        array $files = [],
        array $filesOrder = [],
        array $filesExtends = []
    ): array {
        $realpath = realpath($dir);

        if ($realpath === false) {
            return ['order' => $filesOrder, 'content' => $files, 'extends' => $filesExtends];
        }

        foreach ($this->dirService->getFiles($realpath) as $path) {
            if ($this->fileService->getFileEnding($path) === 'js') {
                $fileContent = "\n/* " . $path . " */\n";
                $fileContent .= file_get_contents($path);
                $pathParts = explode(DIRECTORY_SEPARATOR, mb_substr($path, 0, -3));

                foreach ($pathParts as $key => $pathPart) {
                    unset($pathParts[$key]);

                    if ($pathPart === 'js') {
                        break;
                    }
                }

                $namespace = 'GibsonOS.' . str_replace(DIRECTORY_SEPARATOR, '.', implode('.', $pathParts));
                $files[$namespace] = $fileContent;
                $filesExtends[$namespace] = [];

                if (!array_key_exists($namespace, $filesOrder)) {
                    $filesOrder[$namespace] = 9999;
                }

                preg_match('/extend:\s*[\'|"](GibsonOS\..+?)[\'|"]/', $fileContent, $hits);

                if (array_key_exists(1, $hits)) {
                    $filesExtends[$namespace][] = $hits[1];
                }

                preg_match('/model:\s*[\'|"](GibsonOS\..+?)[\'|"]/', $fileContent, $hits);

                if (array_key_exists(1, $hits)) {
                    $filesExtends[$namespace][] = $hits[1];
                }

                $filesOrder = $this->setOrder($namespace, $filesOrder[$namespace], $filesOrder, $filesExtends);
            } elseif (is_dir($path)) {
                $return = $this->mergeClassFileContent($path, $files, $filesOrder, $filesExtends);
                $filesOrder = $return['order'];
                $files = $return['content'];
                $filesExtends = $return['extends'];
            }
        }

        return ['order' => $filesOrder, 'content' => $files, 'extends' => $filesExtends];
    }

    private function setOrder(string $namespace, int $order, array $filesOrder, array $filesExtends): array
    {
        if (
            isset($filesOrder[$namespace]) &&
            $filesOrder[$namespace] < $order
        ) {
            return $filesOrder;
        }

        $filesOrder[$namespace] = $order;

        if (!isset($filesExtends[$namespace])) {
            return $filesOrder;
        }

        foreach ($filesExtends[$namespace] as $fileExtend) {
            $filesOrder = $this->setOrder($fileExtend, $order - 1, $filesOrder, $filesExtends);
        }

        return $filesOrder;
    }

    private function getClassFilesContent(array $files, array $orders): string
    {
        asort($orders, SORT_NUMERIC);

        foreach ($orders as $namespace => $priority) {
            if (!array_key_exists($namespace, $files)) {
                unset($orders[$namespace]);

                continue;
            }
        }

        $return = '';

        foreach ($orders as $namespace => $priority) {
            $return .= $files[$namespace];
        }

        return $return;
    }
}
