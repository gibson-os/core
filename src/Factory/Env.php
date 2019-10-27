<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Env as EnvService;

class Env
{
    public static function create(): EnvService
    {
        return new EnvService();
    }
}
