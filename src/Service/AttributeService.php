<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Dto\Attribute;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Service\Attribute\AbstractActionAttributeService;
use ReflectionAttribute;
use ReflectionMethod;

class AttributeService
{
    public function __construct(private ServiceManagerService $serviceManagerService)
    {
    }

    /**
     * @throws FactoryError
     *
     * @return Attribute[]
     */
    public function getMethodAttributes(ReflectionMethod $reflectionMethod): array
    {
        $attributesClasses = [];
        $attributes = $reflectionMethod->getAttributes(
            AttributeInterface::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($attributes as $attribute) {
            /** @var AttributeInterface $attributeClass */
            $attributeClass = $attribute->newInstance();
            /** @var AbstractActionAttributeService $attributeService */
            $attributeService = $this->serviceManagerService->get(
                $attributeClass->getAttributeServiceName(),
                AbstractActionAttributeService::class
            );

            $attributesClasses[] = new Attribute($attributeClass, $attributeService);
        }

        return $attributesClasses;
    }
}
