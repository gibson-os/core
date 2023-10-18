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
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Wrapper\ModelWrapper;
use InvalidArgumentException;
use JsonException;
use MDO\Client;
use MDO\Dto\Query\Where;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Manager\TableManager;
use MDO\Query\SelectQuery;
use ReflectionException;
use ReflectionParameter;

class ModelFetcherAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        private readonly TableManager $tableManager,
        private readonly ModelManager $modelManager,
        private readonly RequestService $requestService,
        private readonly ReflectionManager $reflectionManager,
        private readonly SessionService $sessionService,
        private readonly Client $client,
        private readonly ModelWrapper $modelWrapper,
    ) {
    }

    /**
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     * @throws SelectError
     * @throws ClientException
     * @throws RecordException
     */
    public function replace(
        AttributeInterface $attribute,
        array $parameters,
        ReflectionParameter $reflectionParameter,
    ): ?AbstractModel {
        if (!$attribute instanceof GetModel) {
            throw new MapperException(sprintf(
                'Attribute "%s" is not an instance of "%s"!',
                $attribute::class,
                GetModel::class,
            ));
        }

        $modelClassName = $this->reflectionManager->getNonBuiltinTypeName($reflectionParameter);

        if (!is_subclass_of($modelClassName, AbstractModel::class)) {
            throw new InvalidArgumentException(sprintf(
                'Model "%s" is no instance of "%s"!',
                $modelClassName,
                AbstractModel::class,
            ));
        }

        $model = new $modelClassName($this->modelWrapper);

        try {
            $whereParameters = $this->getWhereValues($attribute);
        } catch (RequestError) {
            return null;
        }

        $table = $this->tableManager->getTable($model->getTableName());
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where(
                implode(' AND ', array_map(
                    fn (string $field): string => '`' . $field . '`=?',
                    array_keys($attribute->getConditions()),
                )),
                $whereParameters,
            ))
            ->setLimit(1)
        ;

        try {
            $result = $this->client->execute($selectQuery);
        } catch (ClientException) {
            $result = null;
        }

        $record = $result?->iterateRecords()->current();

        if ($record === null) {
            if ($reflectionParameter->allowsNull()) {
                return null;
            }

            throw (new SelectError(sprintf(
                'Model of type "%s" for parameter "%s" not found!',
                $modelClassName,
                $reflectionParameter->getName(),
            )))->setTable($table);
        }

        $this->modelManager->loadFromRecord($record, $model);

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

                for ($i = 2; $i < $count; ++$i) {
                    if (is_array($value)) {
                        $value = $value[$conditionParts[$i]];

                        continue;
                    }

                    if (!is_object($value)) {
                        throw new MapperException(sprintf(
                            'Value for %s is no object or array',
                            $conditionParts[$i],
                        ));
                    }

                    $reflectionClass = $this->reflectionManager->getReflectionClass($value);
                    $value = $this->reflectionManager->getProperty(
                        $reflectionClass->getProperty($conditionParts[$i]),
                        $value,
                    );
                }

                $values[] = $value;
            }

            if ($conditionParts[0] === 'value') {
                $values[] = $conditionParts[1];
            }
        }

        return $values;
    }
}
