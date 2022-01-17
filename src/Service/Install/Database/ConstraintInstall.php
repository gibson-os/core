<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Install\Database;

use Generator;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\Attribute\TableAttribute;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\EnvService;
use GibsonOS\Core\Service\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use mysqlDatabase;
use Psr\Log\LoggerInterface;
use ReflectionAttribute;
use ReflectionClass;

class ConstraintInstall extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        DirService $dirService,
        ServiceManagerService $serviceManagerService,
        EnvService $envService,
        LoggerInterface $logger,
        private mysqlDatabase $mysqlDatabase,
        private TableAttribute $tableAttribute
    ) {
        parent::__construct($dirService, $serviceManagerService, $envService, $logger);
    }

    public function install(string $module): Generator
    {
        $path = $this->dirService->addEndSlash($module) . 'src' . DIRECTORY_SEPARATOR . 'Model';

        foreach ($this->getFiles($path) as $file) {
            $className = $this->serviceManagerService->getNamespaceByPath($file);
            $reflectionClass = new ReflectionClass($className);
            $tableAttributes = $reflectionClass->getAttributes(Table::class);

            if (count($tableAttributes) === 0) {
                continue;
            }

            /** @var ModelInterface $model */
            $model = new $className();
            $tableName = $model->getTableName();
            $constraints = [];

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                /** @psalm-suppress UndefinedMethod */
                if ($reflectionProperty->getType()?->isBuiltin() ?? false) {
                    continue;
                }

                $constraintAttributes = $reflectionProperty->getAttributes(Constraint::class, ReflectionAttribute::IS_INSTANCEOF);

                if (count($constraintAttributes) === 0) {
                    continue;
                }

                /** @var Constraint $constraintAttribute */
                $constraintAttribute = $constraintAttributes[0]->newInstance();
                /** @psalm-suppress UndefinedMethod */
                $parentClassName = $reflectionProperty->getType()?->getName();
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
                    'AND `COLUMN_NAME`=? ' .
                    'AND `REFERENCED_TABLE_NAME`=? ' .
                    'AND `REFERENCED_COLUMN_NAME`=?'
                ;

                if (!$this->mysqlDatabase->execute($query, [$tableName, $foreignKey, $parentTableName, $parentColumn])) {
                    throw new InstallException(sprintf(
                        'Get constraints for table "%s" failed! Error: %s',
                        $tableName,
                        $this->mysqlDatabase->error()
                    ));
                }

                if ($this->mysqlDatabase->fetchResult() > 0) {
                    continue;
                }

                $constraints[] =
                    'ADD CONSTRAINT `' . $constraintName . '` ' .
                    'FOREIGN KEY (`' . $foreignKey . '`) ' .
                    'REFERENCES `' . $parentTableName . '` ' .
                    '(`' . $parentColumn . '`) ' .
                    'ON UPDATE ' . $constraintAttribute->getOnUpdate() . ' ' .
                    'ON DELETE ' . $constraintAttribute->getOnDelete()
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
                    $this->mysqlDatabase->error()
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
