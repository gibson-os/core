<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetObjects;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Mapper\ObjectMapper;
use JsonException;
use ReflectionException;
use ReflectionParameter;

class ObjectsMapperAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        private readonly ObjectMapperAttribute $objectMapperAttribute,
        private readonly ObjectMapper $objectMapper,
    ) {
    }

    /**
     * @throws MapperException
     * @throws ReflectionException
     * @throws JsonException
     * @throws FactoryError
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): array
    {
        if (!$attribute instanceof GetObjects) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetObjects::class,
            ));
        }

        $parameterFromRequest = $this->objectMapperAttribute->getParameterFromRequest($reflectionParameter);
        $objects = [];
        $objectClassName = $attribute->getClassName();

        foreach (is_array($parameterFromRequest) ? $parameterFromRequest : [] as $requestValues) {
            $objects[] = $this->objectMapper->mapToObject($objectClassName, $requestValues);
        }

        return $objects;
    }
}
