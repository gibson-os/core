<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;

interface ServiceAttributeServiceInterface
{
    public function beforeConstruct(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array;
}
