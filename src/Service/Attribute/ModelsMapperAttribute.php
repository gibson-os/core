<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetMappedModels;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use JsonException;
use ReflectionException;
use ReflectionParameter;

class ModelsMapperAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(private ModelMapperAttribute $modelMapperAttribute)
    {
    }

    /**
     * @throws SelectError
     * @throws ReflectionException
     * @throws MapperException
     * @throws JsonException
     * @throws FactoryError
     * @throws RequestError
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): mixed
    {
        if (!$attribute instanceof GetMappedModels) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetMappedModels::class
            ));
        }

        $models = [];

        foreach ($parameters as $parameter) {
            $models[] = $this->modelMapperAttribute->replace(
                new GetMappedModel($attribute->getConditions(), $attribute->getMapping()),
                $parameter,
                $reflectionParameter
            );
        }

        return $models;
    }
}
