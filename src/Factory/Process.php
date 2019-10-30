<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Process as ProcessService;

class Process
{
    public static function create(): ProcessService
    {
        return new ProcessService();
    }
}
