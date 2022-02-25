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
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use ReflectionException;
use ReflectionParameter;

class ModelMapperAttribute extends ObjectMapperAttribute
{
    public function __construct(
        ObjectMapper $objectMapper,
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

        $model = $this->modelFetcherAttribute->replace(
            new GetModel($attribute->getConditions()),
            $parameters,
            $reflectionParameter
        );

        if ($model === null) {
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
                Constraint::class
            );

            if ($constraintAttribute === null) {
                continue;
            }

            $parentModelClassName = $constraintAttribute->getParentModelClassName()
                ?? $this->reflectionManager->getNonBuiltinTypeName($reflectionProperty)
            ;

            if ($parentModelClassName === null || !class_exists($parentModelClassName)) {
                throw new MapperException(sprintf('"%s"" is no class!', $parentModelClassName ?? 'null'));
            }

            $this->reflectionManager->setProperty(
                $reflectionProperty,
                $model,
                $this->objectMapper->mapToObject(
                    $parentModelClassName,
                    JsonUtility::decode($this->requestService->getRequestValue($this->getRequestKey($attribute, $reflectionProperty)))
                )
            );
        }

        return $model;
    }
}
