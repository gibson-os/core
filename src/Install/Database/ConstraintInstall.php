<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Database;

use Generator;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\Attribute\TableNameAttribute;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use mysqlDatabase;
use ReflectionAttribute;
use ReflectionException;

class ConstraintInstall extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManager $serviceManagerService,
        private mysqlDatabase $mysqlDatabase,
        private TableNameAttribute $tableAttribute,
        private ReflectionManager $reflectionManager,
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws InstallException
     * @throws FactoryError
     * @throws GetError
     * @throws ReflectionException
     */
    public function install(string $module): Generator
    {
        $path = $this->dirService->addEndSlash($module) . 'src' . DIRECTORY_SEPARATOR . 'Model';

        foreach ($this->getFiles($path) as $file) {
            $className = $this->serviceManagerService->getNamespaceByPath($file);
            $reflectionClass = $this->reflectionManager->getReflectionClass($className);

            if (!$this->reflectionManager->getAttribute($reflectionClass, Table::class)) {
                continue;
            }

            /** @var ModelInterface $model */
            $model = new $className();
            $tableName = $model->getTableName();
            $constraints = [];

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                if ($this->reflectionManager->isBuiltin($reflectionProperty)) {
                    continue;
                }

                $constraintAttribute = $this->reflectionManager->getAttribute(
                    $reflectionProperty,
                    Constraint::class,
                    ReflectionAttribute::IS_INSTANCEOF,
                );

                if ($constraintAttribute === null) {
                    continue;
                }

                $parentClassName = $this->reflectionManager->getNonBuiltinTypeName($reflectionProperty);
                /** @var ModelInterface $parentModel */
                $parentModel = new $parentClassName();
                $parentTableName = $parentModel->getTableName();
                $constraintName = $constraintAttribute->getName() ?? 'fk' . ucfirst($tableName) . ucfirst($parentTableName);
                $foreignKey = $constraintAttribute->getOwnColumn()
                    ?? $this->tableAttribute->transformName($reflectionProperty->getName()) . '_id'
                ;
                $parentColumn = $constraintAttribute->getParentColumn();
                $query =
                    'SELECT COUNT(`CONSTRAINT_NAME`) ' .
                    'FROM `information_schema`.`KEY_COLUMN_USAGE` ' .
                    'WHERE `TABLE_NAME`=? ' .
                    'AND `CONSTRAINT_SCHEMA`=? ' .
                    'AND `COLUMN_NAME`=? ' .
                    'AND `REFERENCED_TABLE_NAME`=? ' .
                    'AND `REFERENCED_COLUMN_NAME`=?'
                ;
                $parameters = [
                    $tableName,
                    $this->envService->getString('MYSQL_DATABASE'),
                    $foreignKey,
                    $parentTableName,
                    $parentColumn,
                ];

                if (!$this->mysqlDatabase->execute($query, $parameters)) {
                    throw new InstallException(sprintf(
                        'Get constraints for table "%s" failed! Error: %s',
                        $tableName,
                        $this->mysqlDatabase->error(),
                    ));
                }

                if ($this->mysqlDatabase->fetchResult() > 0) {
                    continue;
                }

                $onUpdate = $constraintAttribute->getOnUpdate();
                $onDelete = $constraintAttribute->getOnDelete();
                $constraints[] =
                    'ADD CONSTRAINT `' . $constraintName . '` ' .
                    'FOREIGN KEY (`' . $foreignKey . '`) ' .
                    'REFERENCES `' . $parentTableName . '` ' .
                    '(`' . $parentColumn . '`) ' .
                    ($onUpdate === null ? '' : 'ON UPDATE ' . $onUpdate . ' ') .
                    ($onDelete === null ? '' : 'ON DELETE ' . $onDelete)
                ;
            }

            if (count($constraints) === 0) {
                continue;
            }

            $query = 'ALTER TABLE `' . $tableName . '` ' . implode(', ', $constraints);
            $this->logger->debug($query);

            if (!$this->mysqlDatabase->sendQuery($query)) {
                throw new InstallException(sprintf(
                    'Add constraints for table "%s" failed! Error: %s',
                    $tableName,
                    $this->mysqlDatabase->error(),
                ));
            }

            yield new Success(sprintf('Constraints for table "%s" installed!', $tableName));
        }
    }

    public function getPart(): string
    {
        return InstallService::PART_DATABASE;
    }

    public function getPriority(): int
    {
        return 698;
    }
}
