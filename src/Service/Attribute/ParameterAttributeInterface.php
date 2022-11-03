<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;

interface ParameterAttributeInterface
{
    public function replace(AttributeInterface $attribute, array $parameters, \ReflectionParameter $reflectionParameter): mixed;
}
