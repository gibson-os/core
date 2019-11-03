<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Env;

class EnvFactory
{
    public static function create(): Env
    {
        return new Env();
    }
}
