<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use InvalidArgumentException;
use JsonException;
use mysqlDatabase;
use mysqlTable;
use ReflectionException;
use ReflectionParameter;

class ModelFetcherAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        private readonly mysqlDatabase $mysqlDatabase,
        private readonly ModelManager $modelManager,
        private readonly RequestService $requestService,
        private readonly ReflectionManager $reflectionManager,
        private readonly SessionService $sessionService,
    ) {
    }

    /**
     * @throws SelectError
     * @throws ReflectionException
     * @throws JsonException
     * @throws MapperException
     * @throws RequestError
     */
    public function replace(
        AttributeInterface $attribute,
        array $parameters,
        ReflectionParameter $reflectionParameter
    ): ?AbstractModel {
        if (!$attribute instanceof GetModel) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetModel::class
            ));
        }

        $modelClassName = $this->reflectionManager->getNonBuiltinTypeName($reflectionParameter);
        $model = new $modelClassName();

        if (!$model instanceof AbstractModel) {
            throw new InvalidArgumentException(sprintf(
                'Model "%s" is no instance of "%s"!',
                $model::class,
                ModelInterface::class
            ));
        }

        try {
            $whereParameters = $this->getWhereValues($attribute);
        } catch (RequestError) {
            return null;
        }

        $table = (new mysqlTable($this->mysqlDatabase, $model->getTableName()))
            ->setWhereParameters($whereParameters)
            ->setWhere(implode(' AND ', array_map(
                fn (string $field): string => '`' . $field . '`=?',
                array_keys($attribute->getConditions())
            )))
            ->setLimit(1)
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

            throw (new SelectError(sprintf(
                'Model of type "%s" for parameter "%s" not found!',
                $modelClassName,
                $reflectionParameter->getName()
            )))->setTable($table);
        }

        $this->modelManager->loadFromMysqlTable($table, $model);

        return $model;
    }

    /**
     * @throws ReflectionException
     * @throws RequestError
     */
    private function getWhereValues(GetModel $attribute): array
    {
        $values = [];

        foreach ($attribute->getConditions() as $condition) {
            $conditionParts = explode('.', $condition);
            $count = count($conditionParts);

            if ($count === 1) {
                $values[] = $this->requestService->getRequestValue($condition);

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
            }
        }

        return $values;
    }
}
