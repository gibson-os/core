<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\SessionService;
use InvalidArgumentException;
use JsonException;
use mysqlDatabase;
use mysqlTable;
use ReflectionException;
use ReflectionParameter;

class ModelsFetcherAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        private readonly mysqlDatabase $mysqlDatabase,
        private readonly ReflectionManager $reflectionManager,
        private readonly SessionService $sessionService,
        private readonly ObjectMapperAttribute $objectMapperAttribute
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
        ReflectionParameter $reflectionParameter
    ): ?array {
        if (!$attribute instanceof GetModels) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetModels::class
            ));
        }

        $modelClassName = $attribute->getClassName();
        $model = new $modelClassName();

        if (!$model instanceof AbstractModel) {
            throw new InvalidArgumentException(sprintf(
                'Model "%s" is no instance of "%s"!',
                $model::class,
                ModelInterface::class
            ));
        }

        $whereParameters = [];
        $where = [];
        $parameterFromRequest = $this->objectMapperAttribute->getParameterFromRequest($reflectionParameter);

        foreach (is_array($parameterFromRequest) ? $parameterFromRequest : [] as $requestValue) {
            array_push(
                $whereParameters,
                ...$this->getWhereValuesForModel($attribute, $requestValue, $parameters)
            );
            $where[] = implode(' AND ', array_map(
                fn (string $field): string => '`' . $field . '`=?',
                array_keys($attribute->getConditions())
            ));
        }

        $table = (new mysqlTable($this->mysqlDatabase, $model->getTableName()))
            ->setWhereParameters($whereParameters)
            ->setWhere('(' . implode(') OR (', $where) . ')')
        ;
        $select = $table->selectPrepared();

        if ($select === false) {
            throw (new SelectError(sprintf(
                'Model query of type "%s" for parameter "%s" has errors! Error: %s',
                $modelClassName,
                $reflectionParameter->getName(),
                $table->connection->error()
            )))->setTable($table);
        }

        if ($select === 0) {
            if ($reflectionParameter->allowsNull()) {
                return null;
            }

            return [];
        }

        $models = [];

        do {
            $model->loadFromMysqlTable($table);
            $models[] = $model;
            /** @var AbstractModel $model */
            $model = new $modelClassName();
        } while ($table->next());

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
                        $value
                    );
                }

                continue;
            }

            $object = $parameters[$conditionParts[0]] ?? $requestValue[$conditionParts[0]];

            if (is_object($object)) {
                $reflectionClass = $this->reflectionManager->getReflectionClass($object);
                $values[] = $this->reflectionManager->getProperty(
                    $reflectionClass->getProperty($conditionParts[1]),
                    $object
                );
            }
        }

        return $values;
    }
}
