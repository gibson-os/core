<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Css;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\User\PermissionViewRepository;

class CssService
{
    private string $vendorPath;

    public function __construct(
        private readonly PermissionViewRepository $permissionViewRepository,
        private readonly DirService $dirService,
        private readonly FileService $fileService,
        private readonly PermissionService $permissionService,
    ) {
        $this->vendorPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..',
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
                'css' . DIRECTORY_SEPARATOR,
            ));
        }

        return implode('', $files);
    }

    /**
     * @throws GetError
     */
    public function getByUserIdAndTask(?int $userId, string $module, string $task): string
    {
        if ($this->permissionService->isDenied($module, $task, userId: $userId)) {
            return '';
        }

        $files = $this->getFiles(
            $this->vendorPath .
            'gibson-os' . DIRECTORY_SEPARATOR .
            $module . DIRECTORY_SEPARATOR .
            'assets' . DIRECTORY_SEPARATOR .
            'css' . DIRECTORY_SEPARATOR .
            $task . DIRECTORY_SEPARATOR,
        );

        return implode('', $files);
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
