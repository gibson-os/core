<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install;

use Generator;
use GibsonOS\Core\Dto\Install\InstallDtoInterface;

interface InstallInterface
{
    /**
     * @return Generator<InstallDtoInterface>
     */
    public function install(string $module): Generator;

    public function getPart(): string;
}
