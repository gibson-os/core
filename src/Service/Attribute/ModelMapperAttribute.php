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
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use ReflectionAttribute;
use ReflectionException;
use ReflectionParameter;

class ModelMapperAttribute extends ObjectMapperAttribute
{
    public function __construct(
        ModelMapper $objectMapper,
        RequestService $requestService,
        ReflectionManager $reflectionManager,
        private ModelFetcherAttribute $modelFetcherAttribute
    ) {
        parent::__construct($objectMapper, $requestService, $reflectionManager);
    }

    /**
     * @throws RequestError
     * @throws SelectError
     * @throws FactoryError
     * @throws MapperException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): AbstractModel
    {
        if (!$attribute instanceof GetMappedModel) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetMappedModel::class
            ));
        }

        try {
            $model = $this->modelFetcherAttribute->replace(
                new GetModel($attribute->getConditions()),
                $parameters,
                $reflectionParameter
            );
        } catch (SelectError) {
            $modelClassName = $this->reflectionManager->getNonBuiltinTypeName($reflectionParameter);
            $model = new $modelClassName();
        }

        if (!$model instanceof AbstractModel) {
            throw new MapperException(sprintf(
                'Model "%s" is not an instance of "%s"!',
                $model::class,
                AbstractModel::class
            ));
        }

        $this->objectMapper->setObjectValues(
            $model,
            $this->getObjectParameters($attribute, $model::class, $parameters)
        );

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
                ?? $this->reflectionManager->getNonBuiltinTypeName($reflectionProperty)
            ;

            if (!class_exists($parentModelClassName)) {
                throw new MapperException(sprintf('"%s" is no class!', $parentModelClassName ?? 'null'));
            }

            $values = JsonUtility::decode($this->requestService->getRequestValue($this->getRequestKey($attribute, $reflectionProperty)));
            $typeName = $this->reflectionManager->getTypeName($reflectionProperty);
            $values = array_map(
                fn ($value): object => $this->objectMapper->mapToObject($parentModelClassName, $value),
                $typeName === 'array' ? $values : [$reflectionProperty->getName() => $values]
            );
            $setter = 'set' . ucfirst($reflectionProperty->getName());
            $model->$setter($typeName === 'array' ? $values : reset($values));
        }

        return $model;
    }
}
