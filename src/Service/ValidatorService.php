<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\Validation\AbstractValidation;
use GibsonOS\Core\Dto\Violation;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Validator\ValidatorInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class ValidatorService
{
    public function __construct(
        private readonly AttributeService $attributeService,
        private readonly ReflectionManager $reflectionManager,
    ) {
    }

    /**
     * @throws ReflectionException
     *
     * @return Violation[]
     */
    public function validate(object $object): array
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($object);
        $violations = $this->getViolations($reflectionClass, $object, $object::class);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $violations = array_merge(
                $violations,
                $this->getViolations(
                    $reflectionProperty,
                    $this->reflectionManager->getProperty($reflectionProperty, $object),
                    $object::class,
                    $reflectionProperty->getName(),
                ),
            );
        }

        return $violations;
    }

    private function getViolations(
        ReflectionClass|ReflectionProperty $reflectionObject,
        mixed $value,
        ?string $objectName,
        ?string $propertyName = null,
    ): array {
        $validationAttributes = $this->attributeService->getAttributesByClassName(
            $reflectionObject,
            AbstractValidation::class,
        );

        $violations = [];

        foreach ($validationAttributes as $validationAttribute) {
            /** @var ValidatorInterface $validator */
            $validator = $validationAttribute->getService();
            /** @var AbstractValidation $validation */
            $validation = $validationAttribute->getAttribute();

            if (!$validator->isValid($validation, $value)) {
                $violations[] = new Violation(
                    $validation->getMessage($value),
                    $validator,
                    $objectName,
                    $propertyName,
                );
            }
        }

        return $violations;
    }
}
