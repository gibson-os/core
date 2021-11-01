<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;

interface AttributeServiceInterface
{
    public function evaluateAttribute(AttributeInterface $attribute): bool;
}