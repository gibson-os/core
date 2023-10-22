<?php
declare(strict_types=1);

namespace GibsonOS\Core\Mapper\Model;

use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Wrapper\ModelWrapper;
use JsonException;
use MDO\Dto\Record;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use MDO\Extractor\PrimaryKeyExtractor;
use MDO\Manager\TableManager;
use ReflectionException;

class ChildrenMapper
{
    public function __construct(
        private readonly ModelManager $modelManager,
        private readonly ReflectionManager $reflectionManager,
        private readonly TableManager $tableManager,
        private readonly ModelWrapper $modelWrapper,
        private readonly PrimaryKeyExtractor $primaryKeyExtractor,
    ) {
    }

    /**
     * @param ChildrenMapping[] $children
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws ClientException
     * @throws RecordException
     */
    public function getChildrenModels(
        Record $record,
        AbstractModel $model,
        array $children,
        array $loadedRecords = [],
    ): void {
        $modelReflection = $this->reflectionManager->getReflectionClass($model::class);

        foreach ($children as $child) {
            $propertyName = $child->getPropertyName();
            $propertyReflection = $modelReflection->getProperty($propertyName);
            $isArray = false;
            $uppercasePropertyName = ucfirst($propertyName);

            try {
                $childModelClassName = $this->reflectionManager->getNonBuiltinTypeName($propertyReflection);
                $setter = sprintf('set%s', $uppercasePropertyName);
            } catch (ReflectionException) {
                $childModelClassName = $this->reflectionManager
                    ->getAttribute($propertyReflection, Constraint::class)
                    ?->getParentModelClassName()
                ;
                $isArray = true;
                $setter = sprintf('add%s', $uppercasePropertyName);
            }

            if ($childModelClassName === null) {
                throw new ReflectionException(sprintf(
                    'No child class name found for property "%s::%s"',
                    $model::class,
                    $propertyName,
                ));
            }

            /** @var AbstractModel $childModel */
            $childModel = new $childModelClassName($this->modelWrapper);
            $tableName = $childModel->getTableName();
            $key = $tableName . implode(
                '_',
                $this->primaryKeyExtractor->extractFromRecord(
                    $this->tableManager->getTable($tableName),
                    $record,
                    $child->getPrefix(),
                ),
            );

            if (!isset($loadedRecords[$key])) {
                $this->modelManager->loadFromRecord($record, $childModel, $child->getPrefix());
                $loadedRecords[$key] = $childModel;
            }

            $childModel = $loadedRecords[$key];

            $this->getChildrenModels(
                $record,
                $childModel,
                $child->getChildren(),
                $loadedRecords,
            );

            if ($isArray) {
                $childModel = [$childModel];
            }

            $model->$setter($childModel);
        }
    }
}
