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
        return $this->getMethodAttributesByClassName($reflectionMethod, AttributeInterface::class);
    }

    /**
     * @param class-string $attributeClassName
     *
     * @throws FactoryError
     *
     * @return Attribute[]
     */
    public function getMethodAttributesByClassName(ReflectionMethod $reflectionMethod, string $attributeClassName): array
    {
        $attributesClasses = [];
        $attributes = $reflectionMethod->getAttributes(
            $attributeClassName,
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
