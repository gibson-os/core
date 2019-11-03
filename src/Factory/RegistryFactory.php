<?php
declare(strict_types=1);

namespace GibsonOS\Core\Factory;

use GibsonOS\Core\Service\Registry;

/**
 * @deprecated
 */
class RegistryFactory
{
    /**
     * @return Registry
     */
    public static function create(): Registry
    {
        /** @var Registry $registry */
        $registry = Registry::getInstance();

        return $registry;
    }
}
