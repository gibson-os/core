<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install;

interface InstallInterface
{
    public function install(string $module): void;

    public function getPart(): string;
}
