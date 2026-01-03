<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
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
use MDO\Manager\TableManager;
use MDO\Query\SelectQuery;
use Override;
use ReflectionException;
use ReflectionParameter;

class ModelFetcherAttribute implements AttributeServiceInterface, ParameterAttributeInterface
{
    public function __construct(
        private readonly TableManager $tableManager,
        private readonly ModelManager $modelManager,
        private readonly ReflectionManager $reflectionManager,
        private readonly Client $client,
        private readonly ModelWrapper $modelWrapper,
        private readonly AttributeParameterTransformer $attributeParameterTransformer,
        private readonly ChildrenQuery $childrenQuery,
        private readonly ChildrenMapper $childrenMapper,
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
    #[Override]
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
            throw new MapperException(sprintf(
                'Model "%s" is no instance of "%s"!',
                $modelClassName,
                AbstractModel::class,
            ));
        }

        $model = new $modelClassName($this->modelWrapper);
        $whereParameters = array_values(
            $this->attributeParameterTransformer->transform($attribute->getConditions()),
        );

        if (count($whereParameters) !== count(array_filter($whereParameters))) {
            return null;
        }

        $table = $this->tableManager->getTable($model->getTableName());
        $alias = $attribute->getAlias();
        $selectQuery = (new SelectQuery($table, $alias))
            ->addWhere(new Where(
                implode(' AND ', array_map(
                    fn (string $field): string => sprintf('`%s`.`%s`=?', $alias, $field),
                    array_keys($attribute->getConditions()),
                )),
                $whereParameters,
            ))
        ;
        $selectQuery = $this->childrenQuery->extend(
            $selectQuery,
            $modelClassName,
            $attribute->getExtends(),
        );

        try {
            $result = $this->client->execute($selectQuery);
        } catch (ClientException) {
            $result = null;
        }

        $modelLoaded = false;

        foreach ($result?->iterateRecords() ?? [] as $record) {
            if (!$modelLoaded) {
                $this->modelManager->loadFromRecord($record, $model);
                $modelLoaded = true;
            }

            $this->childrenMapper->getChildrenModels($record, $model, $attribute->getExtends());
        }

        if (!$modelLoaded) {
            if ($reflectionParameter->allowsNull()) {
                return null;
            }

            throw (new SelectError(sprintf(
                'Model of type "%s" for parameter "%s" not found!',
                $modelClassName,
                $reflectionParameter->getName(),
            )))->setTable($table);
        }

        return $model;
    }
}
