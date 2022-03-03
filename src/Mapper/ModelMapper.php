<?php
declare(strict_types=1);

namespace GibsonOS\Core\Mapper;

use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use ReflectionAttribute;
use ReflectionException;

class ModelMapper extends ObjectMapper
{
    public function __construct(
        private ServiceManager $serviceManagerService,
        private ReflectionManager $reflectionManager
    ) {
        parent::__construct($this->serviceManagerService, $this->reflectionManager);
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     */
    public function setObjectValues(object $object, array $properties): object
    {
        $reflectionClass = $this->reflectionManager->getReflectionClass($object);

        foreach ($properties as $key => $value) {
            try {
                $reflectionProperty = $reflectionClass->getProperty($key);
            } catch (ReflectionException) {
                continue;
            }

            $constraintAttribute = $this->reflectionManager->getAttribute(
                $reflectionProperty,
                Constraint::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            $setter = 'set' . ucfirst($key);

            if ($constraintAttribute !== null) {
                $typeName = $this->reflectionManager->getTypeName($reflectionProperty);

                if (is_string($value)) {
                    $value = JsonUtility::decode($value);
                }

                $values = array_map(
                    fn ($mapValue) => $this->mapToObject(
                        $constraintAttribute->getParentModelClassName() ?? $this->reflectionManager->getNonBuiltinTypeName($reflectionProperty),
                        is_array($mapValue)
                            ? $mapValue
                            : [$reflectionProperty->getName() => $mapValue]
                    ),
                    $typeName !== 'array' ? [$value] : $value
                );

                $object->$setter($typeName !== 'array' ? reset($values) : $values);

                continue;
            }

            $reflectionParameter = $reflectionClass->getMethod($setter)->getParameters()[0];
            $this->reflectionManager->setProperty(
                $reflectionProperty,
                $object,
                $this->mapValueToObject($reflectionParameter, $this->reflectionManager->castValue($reflectionParameter, $value))
            );
        }

        return $object;
    }
}
