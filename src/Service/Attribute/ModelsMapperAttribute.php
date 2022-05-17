<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetMappedModels;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use JsonException;
use ReflectionAttribute;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;

class ModelsMapperAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        private ModelMapper $objectMapper,
        private RequestService $requestService,
        private ReflectionManager $reflectionManager,
        private ModelsFetcherAttribute $modelsFetcherAttribute,
        private SessionService $sessionService,
        private ObjectMapperAttribute $objectMapperAttribute
    ) {
    }

    /**
     * @throws RequestError
     * @throws SelectError
     * @throws FactoryError
     * @throws MapperException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return AbstractModel[]
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): array
    {
        if (!$attribute instanceof GetMappedModels) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetMappedModel::class
            ));
        }

        try {
            $fetchedModels = $this->modelsFetcherAttribute->replace(
                new GetModels($attribute->getClassName(), $attribute->getConditions()),
                $parameters,
                $reflectionParameter
            );
        } catch (SelectError) {
            $fetchedModels = [];
        }

        $parameterFromRequest = $this->objectMapperAttribute->getParameterFromRequest($reflectionParameter);

        foreach (is_array($parameterFromRequest) ? $parameterFromRequest : [] as $requestValues) {
            $model = null;

            foreach ($fetchedModels ?? [] as $fetchedModel) {
                foreach ($attribute->getConditions() as $property => $condition) {
                    $modelValue = $this->reflectionManager->getProperty(
                        $this->reflectionManager->getReflectionClass($fetchedModel)->getProperty($property),
                        $fetchedModel
                    );

                    if ($modelValue !== $requestValues[$condition]) {
                        continue 2;
                    }
                }

                $model = $fetchedModel;
            }

            $this->objectMapper->setObjectValues($model, $requestValues);
            $reflectionClass = $this->reflectionManager->getReflectionClass($model);

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $constraintAttribute = $this->reflectionManager->getAttribute(
                    $reflectionProperty,
                    Constraint::class,
                    ReflectionAttribute::IS_INSTANCEOF
                );

                if ($constraintAttribute === null) {
                    continue;
                }

                $parentModelClassName = $constraintAttribute->getParentModelClassName()
                    ?? $this->reflectionManager->getNonBuiltinTypeName($reflectionProperty);

                if (!class_exists($parentModelClassName)) {
                    throw new MapperException(sprintf('"%s" is no class!', $parentModelClassName ?? 'NULL'));
                }

                $values = $parameters[$reflectionProperty->getName()]
                    ?? $this->getValues($attribute, $reflectionProperty, $reflectionParameter);

                $typeName = $this->reflectionManager->getTypeName($reflectionProperty);
                $values = array_map(
                    fn ($value): object => is_object($value) ? $value : $this->objectMapper->mapToObject($parentModelClassName, $value),
                    $typeName === 'array' ? $values : [$reflectionProperty->getName() => $values]
                );
                $setter = 'set' . ucfirst($reflectionProperty->getName());
                $model->$setter($typeName === 'array' ? $values : reset($values));
            }

            $models[] = $model;
        }

        return $models;
    }

    private function getValues(
        GetMappedModels $attribute,
        ReflectionProperty $reflectionProperty,
        ReflectionParameter $reflectionParameter
    ): array {
        $values = [];
        $parameterFromRequest = $this->objectMapperAttribute->getParameterFromRequest($reflectionParameter);

        foreach (is_array($parameterFromRequest) ? $parameterFromRequest : [] as $requestValue) {
            array_push(
                $values,
                $this->getValuesForModel($attribute, $reflectionProperty, $requestValue)
            );
        }

        return $values;
    }

    private function getValuesForModel(
        GetMappedModels $attribute,
        ReflectionProperty $reflectionProperty,
        array $requestValue
    ): mixed {
        $mappingKey = $this->objectMapperAttribute->getMappingKey($attribute, $reflectionProperty);
        $conditionParts = explode('.', $mappingKey);
        $count = count($conditionParts);

        if ($count === 1) {
            return $requestValue[$mappingKey] ?? $reflectionProperty->getDefaultValue();
        }

        if ($conditionParts[0] === 'session') {
            $value = $this->sessionService->get($conditionParts[1]);

            if ($count < 3) {
                return $value;
            }

            if (is_object($value)) {
                return $this->reflectionManager->getProperty(
                    $reflectionProperty,
                    $value
                );
            }
        }

        // Muss noch aufgebohrt werden
        return null;
    }
}
