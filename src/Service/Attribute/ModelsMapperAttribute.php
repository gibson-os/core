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
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Transformer\AttributeParameterTransformer;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Override;
use ReflectionAttribute;
use ReflectionException;
use ReflectionParameter;

class ModelsMapperAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        private readonly ModelMapper $objectMapper,
        private readonly ReflectionManager $reflectionManager,
        private readonly ModelsFetcherAttribute $modelsFetcherAttribute,
        private readonly ObjectMapperAttribute $objectMapperAttribute,
        private readonly AttributeParameterTransformer $attributeParameterTransformer,
        private readonly ModelWrapper $modelWrapper,
    ) {
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     * @throws ClientException
     * @throws RecordException
     *
     * @return AbstractModel[]|null
     */
    #[Override]
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): ?array
    {
        if (!$attribute instanceof GetMappedModels) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetMappedModel::class,
            ));
        }

        $modelClassName = $attribute->getClassName();

        if (!is_subclass_of($modelClassName, AbstractModel::class)) {
            throw new MapperException(sprintf(
                'Model "%s" is no instance of "%s"!',
                $modelClassName,
                AbstractModel::class,
            ));
        }

        try {
            $fetchedModels = $this->modelsFetcherAttribute->replace(
                new GetModels($modelClassName, $attribute->getConditions()),
                $parameters,
                $reflectionParameter,
            );
        } catch (SelectError) {
            $fetchedModels = [];
        }

        $conditions = $attribute->getConditions();
        $transformedConditions = $this->attributeParameterTransformer->transform(
            $conditions,
            $reflectionParameter->getName() . '.',
        );
        $parameterFromRequest = $this->objectMapperAttribute->getParameterFromRequest($reflectionParameter);
        $models = [];

        foreach (is_array($parameterFromRequest) ? $parameterFromRequest : [] as $requestValues) {
            /** @var AbstractModel $model */
            $model = new $modelClassName($this->modelWrapper);

            foreach ($fetchedModels ?? [] as $fetchedModel) {
                foreach ($conditions as $property => $condition) {
                    $propertyWithSpace = str_replace('_', ' ', $property);

                    if (!is_string($propertyWithSpace)) {
                        continue;
                    }

                    $modelValue = $this->reflectionManager->getProperty(
                        $this->reflectionManager->getReflectionClass($fetchedModel)->getProperty(
                            lcfirst(str_replace(' ', '', ucwords($propertyWithSpace))),
                        ),
                        $fetchedModel,
                    );

                    if ($modelValue !== ($transformedConditions[$property] ?? ($requestValues[$condition] ?? null))) {
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
                    ReflectionAttribute::IS_INSTANCEOF,
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
                    ?? $requestValues[$reflectionProperty->getName()]
                    ?? []
                ;

                $typeName = $this->reflectionManager->getTypeName($reflectionProperty);
                $values = array_map(
                    fn ($value): object => is_object($value) ? $value : $this->objectMapper->mapToObject($parentModelClassName, $value),
                    $typeName === 'array' ? $values : [$reflectionProperty->getName() => $values],
                );
                $setter = 'set' . ucfirst($reflectionProperty->getName());

                if (
                    reset($values) === null
                    && !$this->reflectionManager->allowsNull($reflectionProperty)
                ) {
                    continue;
                }

                $model->$setter($typeName === 'array' ? $values : reset($values));
            }

            $models[] = $model;
        }

        return $models ?: ($this->reflectionManager->allowsNull($reflectionParameter) ? null : []);
    }
}
