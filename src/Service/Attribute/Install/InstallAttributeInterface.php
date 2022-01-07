<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute\Install;

use GibsonOS\Core\Attribute\AttributeInterface;

interface InstallAttributeInterface
{
    /**
     * @param class-string $className
     */
    public function execute(AttributeInterface $attribute, string $className): void;
}
