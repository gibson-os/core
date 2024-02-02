<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Transformer\AttributeParameterTransformer;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionAttribute;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;

class ModelMapperAttribute extends ObjectMapperAttribute
{
    public function __construct(
        ModelMapper $objectMapper,
        RequestService $requestService,
        ReflectionManager $reflectionManager,
        private readonly ModelFetcherAttribute $modelFetcherAttribute,
        private readonly ModelWrapper $modelWrapper,
        private readonly AttributeParameterTransformer $attributeParameterTransformer,
    ) {
        parent::__construct($objectMapper, $requestService, $reflectionManager);
    }

    /**
     * @throws FactoryError
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     * @throws ClientException
     * @throws RecordException
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): ?AbstractModel
    {
        if (!$attribute instanceof GetMappedModel) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetMappedModel::class,
            ));
        }

        $isLoaded = false;

        try {
            $model = $this->modelFetcherAttribute->replace(
                new GetModel($attribute->getConditions()),
                $parameters,
                $reflectionParameter,
            );
            $isLoaded = true;
        } catch (SelectError) {
            $model = null;
        }

        if ($model === null) {
            if ($reflectionParameter->allowsNull()) {
                return null;
            }

            $model = $this->getModel($reflectionParameter);
        }

        $parameters['modelWrapper'] = $this->modelWrapper;

        try {
            $this->objectMapper->setObjectValues(
                $model,
                $this->getObjectParameters($attribute, $model::class, $parameters),
            );
        } catch (MapperException $exception) {
            if (!$isLoaded) {
                throw $exception;
            }
        }

        $this->loadConstraints($model, $attribute, $parameters);

        return $model;
    }

    /**
     * @throws ReflectionException
     */
    private function getValues(GetMappedModel $attribute, ReflectionProperty $reflectionProperty): mixed
    {
        $mappingKey = $this->getMappingKey($attribute, $reflectionProperty);

        return $this->attributeParameterTransformer->transform([$mappingKey])[0];
    }

    /**
     * @throws FactoryError
     * @throws MapperException
     * @throws JsonException
     * @throws ReflectionException
     */
    private function loadConstraints(
        AbstractModel $model,
        GetMappedModel $attribute,
        array $parameters,
    ): void {
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

            $propertyName = $reflectionProperty->getName();
            $values = $this->getValues($attribute, $reflectionProperty) ?? ($parameters[$propertyName] ?? null);
            $typeName = $this->reflectionManager->getTypeName($reflectionProperty);
            $idGetter = 'get' . ucfirst($constraintAttribute->getOwnColumn() ?? $propertyName . 'id');
            $setter = 'set' . ucfirst($propertyName);

            if (is_array($values) && count($values) !== 0) {
                $values = array_map(
                    fn ($value): object => is_object($value)
                        ? $value
                        : (
                            is_array($value)
                                ? $this->objectMapper->mapToObject($parentModelClassName, $value)
                                : throw new MapperException(sprintf(
                                    'Properties (%s) for object "%s" used for %s->%s() is no array! Maybe map the required object before',
                                    $value === null ? 'null' : (string) $value,
                                    $parentModelClassName,
                                    $model::class,
                                    $setter,
                                ))
                        ),
                    $typeName === 'array' ? $values : [$propertyName => $values],
                );
            }

            if (
                $values === null
                && (!$this->reflectionManager->allowsNull($reflectionProperty) || $model->$idGetter() !== null)
            ) {
                continue;
            }

            $model->$setter(is_array($values) ? ($typeName === 'array' ? $values : reset($values)) : $values);
        }
    }

    /**
     * @throws ReflectionException
     * @throws MapperException
     */
    private function getModel(ReflectionParameter $reflectionParameter): AbstractModel
    {
        $modelClassName = $this->reflectionManager->getNonBuiltinTypeName($reflectionParameter);

        if (!is_subclass_of($modelClassName, AbstractModel::class)) {
            throw new MapperException(sprintf(
                'Model "%s" is not an instance of "%s"!',
                $modelClassName,
                AbstractModel::class,
            ));
        }

        return new $modelClassName($this->modelWrapper);
    }
}
