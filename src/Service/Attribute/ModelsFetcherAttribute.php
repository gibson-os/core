<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Mapper\Model\ChildrenMapper;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Query\ChildrenQuery;
use GibsonOS\Core\Transformer\AttributeParameterTransformer;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Client;
use MDO\Dto\Query\Where;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Extractor\PrimaryKeyExtractor;
use MDO\Manager\TableManager;
use MDO\Query\SelectQuery;
use MDO\Service\SelectService;
use ReflectionException;
use ReflectionParameter;

class ModelsFetcherAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly TableManager $tableManager,
        private readonly ModelManager $modelManager,
        private readonly ModelWrapper $modelWrapper,
        private readonly AttributeParameterTransformer $attributeParameterTransformer,
        private readonly SelectService $selectService,
        private readonly PrimaryKeyExtractor $primaryKeyExtractor,
        private readonly ChildrenQuery $childrenQuery,
        private readonly ChildrenMapper $childrenMapper,
    ) {
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws MapperException
     * @throws ReflectionException
     * @throws SelectError
     * @throws RecordException
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

        if (!is_subclass_of($modelClassName, AbstractModel::class)) {
            throw new MapperException(sprintf(
                'Model "%s" is no instance of "%s"!',
                $modelClassName,
                AbstractModel::class,
            ));
        }

        $model = new $modelClassName($this->modelWrapper);
        $conditions = $this->attributeParameterTransformer->transform(
            $attribute->getConditions(),
            $reflectionParameter->getName() . '.',
        );

        if (count($conditions) !== count(array_filter($conditions))) {
            return $this->getDefaultReturn($reflectionParameter);
        }

        $table = $this->tableManager->getTable($model->getTableName());
        $alias = $attribute->getAlias();
        $selectQuery = new SelectQuery($table, $alias);

        foreach ($conditions as $conditionField => $conditionValue) {
            if (!is_array($conditionValue)) {
                $conditionValue = [$conditionValue];
            }

            $selectQuery->addWhere(new Where(
                sprintf('`%s`.`%s` IN (%s)', $alias, $conditionField, $this->selectService->getParametersString($conditionValue)),
                array_values($conditionValue),
            ));
        }

        $selectQuery = $this->childrenQuery->extend(
            $selectQuery,
            $modelClassName,
            $attribute->getExtends(),
        );

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

        foreach ($result->iterateRecords() as $record) {
            $primaryKey = implode(
                '-',
                $this->primaryKeyExtractor->extractFromRecord(
                    $selectQuery->getTable(),
                    $record,
                ),
            );

            if (!isset($models[$primaryKey])) {
                $model = new $modelClassName($this->modelWrapper);
                $this->modelManager->loadFromRecord($record, $model);
                $models[$primaryKey] = $model;
            }

            $this->childrenMapper->getChildrenModels(
                $record,
                $models[$primaryKey],
                $attribute->getExtends(),
            );
        }

        return $models === []
            ? $this->getDefaultReturn($reflectionParameter)
            : array_values($models)
        ;
    }

    private function getDefaultReturn(ReflectionParameter $reflectionParameter): ?array
    {
        if ($reflectionParameter->allowsNull()) {
            return null;
        }

        return [];
    }
}
