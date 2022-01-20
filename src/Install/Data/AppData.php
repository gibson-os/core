<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class AppsData extends AbstractInstall implements PriorityInterface
{
    public function install(string $module): Generator
    {
        $this->addApp('Laufwerke', 'core', 'drive', 'index', 'icon_harddrive');

        yield new Success('Core apps installed!');
    }

    public function getPart(): string
    {
        return InstallService::PART_DATA;
    }

    public function getModule(): ?string
    {
        return 'core';
    }

    public function getPriority(): int
    {
        return 0;
    }
}
