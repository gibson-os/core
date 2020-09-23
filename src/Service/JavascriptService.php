<?php declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Javascript;
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

    /**
     * @var string
     */
    private $vendorPath;

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
        $this->vendorPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..'
        ) . DIRECTORY_SEPARATOR;
    }

    /**
     * @throws SelectError
     */
    public function getByUserId(?int $userId, string $module = null): string
    {
        $files = [];
        $oldData = '';

        foreach ($this->permissionViewRepository->getTaskList($userId, $module) as $task) {
            $dir =
                'js' . DIRECTORY_SEPARATOR .
                'module' . DIRECTORY_SEPARATOR .
                $task->module . DIRECTORY_SEPARATOR .
                $task->task . DIRECTORY_SEPARATOR
            ;
            /** @var Javascript[] $files */
            $files = array_merge($files, $this->getFiles($dir));

            /** @var Javascript[] $files */
            $files = array_merge($files, $this->getFiles(
                $this->vendorPath .
                'gibson-os' . DIRECTORY_SEPARATOR .
                $task->module . DIRECTORY_SEPARATOR .
                'assets' . DIRECTORY_SEPARATOR .
                'js' . DIRECTORY_SEPARATOR
            ));

            $oldData .= $this->mergeFileContent($dir);
        }

        $content = '';

        foreach ($files as $file) {
            $content .= $this->loadFile($file->getNamespace(), $files);
        }

        return $content . $oldData;
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
        $files = $this->getFiles($dir);

        /** @var Javascript[] $files */
        $files = array_merge($files, $this->getFiles(
            $this->vendorPath .
            'gibson-os' . DIRECTORY_SEPARATOR .
            $module . DIRECTORY_SEPARATOR .
            'assets' . DIRECTORY_SEPARATOR .
            'js' . DIRECTORY_SEPARATOR .
            $task . DIRECTORY_SEPARATOR
        ));

        $content = '';

        foreach ($files as $file) {
            $content .= $this->loadFile($file->getNamespace(), $files);
        }

        return $content . $this->mergeFileContent($dir)
        ;
    }

    /**
     * @return Javascript[]
     */
    private function getFiles(string $dir): array
    {
        $files = [];

        foreach ($this->dirService->getFiles($dir) as $path) {
            if ($this->fileService->getFileEnding($path) === 'js') {
                $content = file_get_contents($path);
                $classname = $this->getClassname($path);

                $files[$classname] = (new Javascript($path, $classname, $content))
                    ->setBeforeLoad($this->getExtends($content))
                ;
            } elseif (is_dir($path)) {
                $files = array_merge($files, $this->getFiles($path));
            }
        }

        return $files;
    }

    private function getClassname(string $path): string
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, mb_substr($path, 0, -3));

        foreach ($pathParts as $key => $pathPart) {
            unset($pathParts[$key]);

            if ($pathPart === 'gibson-os') {
                unset($pathParts[$key + 2], $pathParts[$key + 3]);

                break;
            }

            if ($pathPart === 'module') {
                break;
            }
        }

        return 'GibsonOS.module.' . str_replace(DIRECTORY_SEPARATOR, '.', implode('.', $pathParts));
    }

    /**
     * @return string[]
     */
    private function getExtends(string $content): array
    {
        $extends = [];

        preg_match('/extend:\s*[\'|"](GibsonOS\..+?)[\'|"]/', $content, $hits);

        if (array_key_exists(1, $hits)) {
            $extends[] = $hits[1];
        }

        preg_match('/model:\s*[\'|"](GibsonOS\..+?)[\'|"]/', $content, $hits);

        if (array_key_exists(1, $hits)) {
            $extends[] = $hits[1];
        }

        return $extends;
    }

    /**
     * @param Javascript[] $files
     */
    private function loadFile(string $namespace, array $files): string
    {
        if (!isset($files[$namespace])) {
            return '';
        }

        $javascript = $files[$namespace];

        if ($javascript->isLoaded()) {
            return '';
        }

        $content = '';

        foreach ($javascript->getBeforeLoad() as $item) {
            $content .= $this->loadFile($item, $files);
        }

        $javascript->setLoaded(true);

        return $content . $javascript . PHP_EOL;
    }

    /**
     * @deprecated
     */
    private function mergeFileContent(string $dir): string
    {
        $return = '';

        $filename = $this->dirService->removeEndSlash($dir) . '.js';

        if (file_exists($filename)) {
            $return .= "\n/* " . $filename . " */\n";
            $return .= file_get_contents($filename);
        }

        return $return;
    }
}
