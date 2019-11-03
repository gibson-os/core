<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\ProcessService;

class ProcessFactory
{
    /**
     * @return ProcessService
     */
    public static function create(): ProcessService
    {
        return new ProcessService();
    }
}
