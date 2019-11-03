<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\EnvService;

class EnvFactory
{
    public static function create(): EnvService
    {
        return new EnvService();
    }
}
