<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\SessionService;
use InvalidArgumentException;
use JsonException;
use MDO\Client;
use MDO\Dto\Query\Where;
use MDO\Exception\ClientException;
use MDO\Manager\TableManager;
use MDO\Query\SelectQuery;
use ReflectionException;
use ReflectionParameter;

class ModelsFetcherAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly TableManager $tableManager,
        private readonly ModelManager $modelManager,
        private readonly ReflectionManager $reflectionManager,
        private readonly SessionService $sessionService,
        private readonly ObjectMapperAttribute $objectMapperAttribute,
        private readonly ModelMapper $modelMapper,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws JsonException
     * @throws MapperException
     * @throws SelectError
     *
     * @return AbstractModel[]|null
     */
    public function replace(
        AttributeInterface $attribute,
        array $parameters,
        ReflectionParameter $reflectionParameter,
    ): ?array {
        if (!$attribute instanceof GetModels) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetModels::class,
            ));
        }

        $modelClassName = $attribute->getClassName();
        $model = new $modelClassName();

        if (!$model instanceof AbstractModel) {
            throw new InvalidArgumentException(sprintf(
                'Model "%s" is no instance of "%s"!',
                $model::class,
                ModelInterface::class,
            ));
        }

        $whereParameters = [];
        $where = [];

        try {
            $parameterFromRequest = $this->objectMapperAttribute->getParameterFromRequest($reflectionParameter);
        } catch (MapperException) {
            $parameterFromRequest = [];
        }

        foreach (is_array($parameterFromRequest) ? $parameterFromRequest : [] as $requestValue) {
            array_push(
                $whereParameters,
                ...$this->getWhereValuesForModel($attribute, $requestValue, $parameters),
            );
            $where[] = implode(' AND ', array_map(
                fn (string $field): string => '`' . $field . '`=?',
                array_keys($attribute->getConditions()),
            ));
        }

        if (count($where) === 0) {
            return [];
        }

        $table = $this->tableManager->getTable($model->getTableName());
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where(sprintf('(%s)', implode(') OR (', $where)), $whereParameters))
        ;

        try {
            $result = $this->client->execute($selectQuery);
        } catch (ClientException $exception) {
            throw (new SelectError(
                sprintf(
                    'Model query of type "%s" for parameter "%s" has errors! Error: %s',
                    $modelClassName,
                    $reflectionParameter->getName(),
                    $this->client->getError(),
                ),
                previous: $exception,
            ))->setTable($table);
        }

        $models = [];

        foreach ($result?->iterateRecords() ?? [] as $record) {
            $model = new $modelClassName();
            $this->modelManager->loadFromRecord($record, $model);
            $models[] = $model;
        }

        if (count($models) === 0) {
            if ($reflectionParameter->allowsNull()) {
                return null;
            }

            return [];
        }

        return $models;
    }

    private function getWhereValuesForModel(
        GetModels $attribute,
        array $requestValue,
        array $parameters,
    ): array {
        $values = [];

        foreach ($attribute->getConditions() as $condition) {
            $conditionParts = explode('.', $condition);
            $count = count($conditionParts);

            if ($count === 1) {
                $values[] = $parameters[$condition] ?? $requestValue[$condition];

                continue;
            }

            if ($conditionParts[0] === 'session') {
                $value = $this->sessionService->get($conditionParts[1]);

                if ($count < 3) {
                    $values[] = $value;

                    continue;
                }

                if (is_object($value)) {
                    $reflectionClass = $this->reflectionManager->getReflectionClass($value);
                    $values[] = $this->reflectionManager->getProperty(
                        $reflectionClass->getProperty($conditionParts[2]),
                        $value,
                    );
                }

                continue;
            }

            if ($conditionParts[0] === 'value') {
                $values[] = $conditionParts[1];

                continue;
            }

            $object = $parameters[$conditionParts[0]] ?? $requestValue[$conditionParts[0]];

            if (is_object($object)) {
                $reflectionClass = $this->reflectionManager->getReflectionClass($object);
                $values[] = $this->reflectionManager->getProperty(
                    $reflectionClass->getProperty($conditionParts[1]),
                    $object,
                );
            }
        }

        return $values;
    }
}
