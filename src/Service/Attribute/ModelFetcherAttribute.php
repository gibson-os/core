<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\ModelInterface;
use InvalidArgumentException;
use mysqlDatabase;
use mysqlTable;
use ReflectionParameter;

class ModelFetcherAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(private mysqlDatabase $mysqlDatabase)
    {
    }

    /**
     * @throws SelectError
     */
    public function replace(AttributeInterface $attribute, array $parameters, ReflectionParameter $reflectionParameter): ?AbstractModel
    {
        if (!$attribute instanceof GetModel) {
            return null;
        }

        /** @psalm-suppress UndefinedMethod */
        $modelClassName = $reflectionParameter->getType()?->getName();

        if ($modelClassName === null) {
            return null;
        }

        $model = new $modelClassName();

        if (!$model instanceof AbstractModel) {
            throw new InvalidArgumentException(sprintf(
                'Model "%s" is no instance of "%s"!',
                $model::class,
                ModelInterface::class
            ));
        }

        $table = (new mysqlTable($this->mysqlDatabase, $model->getTableName()))
            ->setWhereParameters(array_values($attribute->getConditions()))
            ->setWhere(implode(' AND ', array_map(
                fn (string $field) => '`' . $field . '`=?',
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

        $model->loadFromMysqlTable($table);

        return $model;
    }
}
