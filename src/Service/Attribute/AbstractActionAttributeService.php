<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Service\Response\ResponseInterface;

abstract class AbstractActionAttributeService
{
    public function preExecute(AttributeInterface $attribute, array $parameters): array
    {
        return $parameters;
    }

    public function postExecute(AttributeInterface $attribute, ResponseInterface $response): void
    {
    }

    public function usedParameters(AttributeInterface $attribute): array
    {
        return [];
    }
}
