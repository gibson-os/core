<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\InstallDtoInterface;
use GibsonOS\Core\Exception\InstallException;

interface InstallInterface
{
    /**
     * @throws InstallException
     *
     * @return Generator<InstallDtoInterface>
     */
    public function install(string $module): Generator;

    public function getPart(): string;

    public function getModule(): ?string;
}
