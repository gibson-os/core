<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install;

interface RequiredExtensionInterface
{
    public function checkRequiredExtensions(): void;
}
