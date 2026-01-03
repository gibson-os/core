<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Css;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Repository\User\PermissionViewRepository;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;

class CssService
{
    private string $vendorPath;

    public function __construct(
        private readonly PermissionViewRepository $permissionViewRepository,
        private readonly DirService $dirService,
        private readonly FileService $fileService,
        private readonly PermissionService $permissionService,
    ) {
        $this->vendorPath = (realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..',
        ) ?: '') . DIRECTORY_SEPARATOR;
    }

    /**
     * @throws GetError
     * @throws ClientException
     * @throws RecordException
     */
    public function getByUserId(?int $userId, ?string $module = null): string
    {
        $files = [];

        foreach ($this->permissionViewRepository->getTaskList($userId, $module) as $task) {
            /** @var Css[] $files */
            $files = array_merge($files, $this->getFiles(
                $this->vendorPath .
                'gibson-os' . DIRECTORY_SEPARATOR .
                $this->transformModuleName((string) $task->get('module')->getValue()) . DIRECTORY_SEPARATOR .
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
                $content = file_get_contents($path);

                if ($content === false) {
                    throw new GetError('Could not read file ' . $path);
                }

                $files[$path] = new Css($path, $content);
            } elseif (is_dir($path)) {
                $files = array_merge($files, $this->getFiles($path));
            }
        }

        return $files;
    }

    private function transformModuleName(string $moduleName): ?string
    {
        return preg_replace_callback(
            '/([a-z])([A-Z])/',
            static fn ($matches) => $matches[1] . '-' . mb_strtolower($matches[2]),
            $moduleName,
        );
    }
}
