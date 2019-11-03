<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory\File;

use GibsonOS\Core\Service\File\Type;

class TypeFactory
{
    /**
     * @return Type
     */
    public static function create(): Type
    {
        return new Type();
    }
}
