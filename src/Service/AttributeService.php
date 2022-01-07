<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Dto\Attribute;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Service\Attribute\AttributeServiceInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionParameter;

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
    public function getAttributes(
        ReflectionMethod|ReflectionClass|ReflectionParameter|ReflectionClassConstant $reflectionObject
    ): array {
        return $this->getAttributesByClassName($reflectionObject, AttributeInterface::class);
    }

    /**
     * @param class-string $attributeClassName
     *
     * @throws FactoryError
     *
     * @return Attribute[]
     */
    public function getAttributesByClassName(
        ReflectionMethod|ReflectionClass|ReflectionParameter|ReflectionClassConstant $reflectionObject,
        string $attributeClassName
    ): array {
        $attributesClasses = [];
        $attributes = $reflectionObject->getAttributes(
            $attributeClassName,
            ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($attributes as $attribute) {
            /** @var AttributeInterface $attributeClass */
            $attributeClass = $attribute->newInstance();
            /** @var AttributeServiceInterface $attributeService */
            $attributeService = $this->serviceManagerService->get(
                $attributeClass->getAttributeServiceName(),
                AttributeServiceInterface::class
            );

            $attributesClasses[] = new Attribute($attributeClass, $attributeService);
        }

        return $attributesClasses;
    }
}
