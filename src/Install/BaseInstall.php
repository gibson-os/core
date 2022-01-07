<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Service\Install\RequiredExtensionInterface;

class BaseInstall implements RequiredExtensionInterface
{
    public function checkRequiredExtensions(): void
    {
        if (!class_exists('SQLite3')) {
            throw new InstallException('Please install PHP SQLite3 extension!');
        }

        if (!class_exists('ZipArchive')) {
            throw new InstallException('Please install PHP Zip extension!');
        }
    }
}
