<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Dto\Attribute;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Service\Attribute\AttributeServiceInterface;

class AttributeService
{
    public function __construct(
        private ServiceManager $serviceManagerService,
        private ReflectionManager $reflectionManager
    ) {
    }

    /**
     * @throws FactoryError
     *
     * @return Attribute[]
     */
    public function getAttributes(
        \ReflectionMethod|\ReflectionClass|\ReflectionParameter|\ReflectionClassConstant $reflectionObject
    ): array {
        return $this->getAttributesByClassName($reflectionObject, AttributeInterface::class);
    }

    /**
     * @param class-string<AttributeInterface> $attributeClassName
     *
     * @throws FactoryError
     *
     * @return Attribute[]
     */
    public function getAttributesByClassName(
        \ReflectionMethod|\ReflectionClass|\ReflectionParameter|\ReflectionClassConstant $reflectionObject,
        string $attributeClassName
    ): array {
        $attributesClasses = [];
        $attributes = $this->reflectionManager->getAttributes(
            $reflectionObject,
            $attributeClassName,
            \ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($attributes as $attribute) {
            /** @var AttributeServiceInterface $attributeService */
            $attributeService = $this->serviceManagerService->get(
                $attribute->getAttributeServiceName(),
                AttributeServiceInterface::class
            );

            $attributesClasses[] = new Attribute($attribute, $attributeService);
        }

        return $attributesClasses;
    }
}
