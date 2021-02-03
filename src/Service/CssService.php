<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Css;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\User\PermissionViewRepository;

class CssService extends AbstractService
{
    private PermissionViewRepository $permissionViewRepository;

    private DirService $dirService;

    private FileService $fileService;

    private PermissionService $permissionService;

    private string $vendorPath;

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
     * @throws GetError
     * @throws SelectError
     */
    public function getByUserId(?int $userId, string $module = null): string
    {
        $files = [];

        foreach ($this->permissionViewRepository->getTaskList($userId, $module) as $task) {
            /** @var Css[] $files */
            $files = array_merge($files, $this->getFiles(
                $this->vendorPath .
                'gibson-os' . DIRECTORY_SEPARATOR .
                $task->module . DIRECTORY_SEPARATOR .
                'assets' . DIRECTORY_SEPARATOR .
                'css' . DIRECTORY_SEPARATOR
            ));
        }

        $content = '';

        foreach ($files as $file) {
            $content .= $file;
        }

        return $content;
    }

    /**
     * @throws GetError
     * @throws SelectError
     * @throws DateTimeError
     */
    public function getByUserIdAndTask(?int $userId, string $module, string $task): string
    {
        if ($this->permissionService->isDenied($module, null, null, $userId)) {
            return '';
        }

        $files = $this->getFiles(
            $this->vendorPath .
            'gibson-os' . DIRECTORY_SEPARATOR .
            $module . DIRECTORY_SEPARATOR .
            'assets' . DIRECTORY_SEPARATOR .
            'css' . DIRECTORY_SEPARATOR .
            $task . DIRECTORY_SEPARATOR
        );

        $content = '';

        foreach ($files as $file) {
            $content .= $file;
        }

        return $content;
    }

    /**
     * @throws GetError
     *
     * @return Css[]
     */
    private function getFiles(string $dir): array
    {
        $files = [];

        foreach ($this->dirService->getFiles($dir) as $path) {
            if ($this->fileService->getFileEnding($path) === 'css') {
                $files[$path] = new Css($path, file_get_contents($path));
            } elseif (is_dir($path)) {
                $files = array_merge($files, $this->getFiles($path));
            }
        }

        return $files;
    }
}
