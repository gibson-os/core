<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class IconInstall extends AbstractInstall implements PriorityInterface
{
    /**
     * @throws CreateError
     * @throws SaveError
     * @throws SelectError
     */
    public function install(string $module): \Generator
    {
        $customIconPath = realpath(
            dirname(__FILE__) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'htdocs' . DIRECTORY_SEPARATOR .
            'img' . DIRECTORY_SEPARATOR .
            'icons' . DIRECTORY_SEPARATOR .
            'custom' . DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;

        if (!file_exists($customIconPath)) {
            $this->dirService->create($customIconPath);
        }

        $this->setSetting('core', 'custom_icon_path', $customIconPath);

        yield new Success(sprintf('Custom icon path set to "%s"!', $customIconPath));
    }

    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    public function getModule(): ?string
    {
        return 'core';
    }

    public function getPriority(): int
    {
        return 500;
    }
}
