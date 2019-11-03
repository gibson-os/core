<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Process;

class ProcessFactory
{
    /**
     * @return Process
     */
    public static function create(): Process
    {
        return new Process();
    }
}
