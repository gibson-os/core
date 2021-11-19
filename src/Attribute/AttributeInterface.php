<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

interface AttributeInterface
{
    /**
     * @return class-string
     */
    public function getAttributeServiceName(): string;
}
