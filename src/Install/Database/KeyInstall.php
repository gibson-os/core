<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Database;

use Generator;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Model\ModelInterface;
use GibsonOS\Core\Service\Attribute\TableAttribute;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use function mb_substr;
use mysqlDatabase;
use ReflectionClass;
use ReflectionException;

class KeyInstall extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManagerService $serviceManagerService,
        private mysqlDatabase $mysqlDatabase,
        private TableAttribute $tableAttribute
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
            $reflectionClass = new ReflectionClass($className);
            $tableAttributes = $reflectionClass->getAttributes(Table::class);
            $installedKeys = ['PRIMARY'];

            if (count($tableAttributes) === 0) {
                continue;
            }

            /** @var ModelInterface $model */
            $model = new $className();
            $tableName = $model->getTableName();
            $keyAttributes = $reflectionClass->getAttributes(Key::class);

            foreach ($keyAttributes as $keyAttributeItem) {
                /** @var Key $keyAttribute */
                $keyAttribute = $keyAttributeItem->newInstance();

                if (count($keyAttribute->getColumns()) === 0) {
                    throw new InstallException(sprintf(
                        'Key attribute for model "%s" has no columns defined!',
                        $className
                    ));
                }

                $installedKeys[] = $this->installKey($tableName, $keyAttribute);
            }

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $keyAttributes = $reflectionProperty->getAttributes(Key::class);

                foreach ($keyAttributes as $keyAttributeItem) {
                    /** @var Key $keyAttribute */
                    $keyAttribute = $keyAttributeItem->newInstance();

                    if (count($keyAttribute->getColumns()) !== 0) {
                        throw new InstallException(sprintf(
                            'Key attribute for property "%s::%s" is not empty!',
                            $className,
                            $reflectionProperty->getName()
                        ));
                    }

                    $keyAttribute->setColumns([$this->tableAttribute->transformName($reflectionProperty->getName())]);
                    $installedKeys[] = $this->installKey($tableName, $keyAttribute);
                }
            }

            $installedKeys = array_filter($installedKeys);
            $query =
                'SHOW INDEX FROM `' . $tableName . '` WHERE `Key_name` NOT IN (' .
                    'SELECT `CONSTRAINT_NAME` ' .
                    'FROM `information_schema`.`KEY_COLUMN_USAGE` ' .
                    'WHERE `TABLE_SCHEMA`=? ' .
                    'AND `TABLE_NAME`=? ' .
                    'AND `POSITION_IN_UNIQUE_CONSTRAINT` IS NOT NULL' .
                ') AND `Key_name` NOT IN (' .
                    implode(', ', array_fill(0, count($installedKeys), '?')) .
                ')'
            ;
            $parameters = array_merge(
                [$this->envService->getString('MYSQL_DATABASE'), $tableName],
                $installedKeys
            );

            if (!$this->mysqlDatabase->execute($query, $parameters)) {
                throw new InstallException(sprintf(
                    'Show indexes from table "%s" failed! Error: %s',
                    $tableName,
                    $this->mysqlDatabase->error()
                ));
            }

            foreach ($this->mysqlDatabase->fetchObjectList() as $index) {
                $query = 'DROP INDEX `' . $index->Key_name . '` ON `' . $tableName . '`';
                $this->logger->debug($query);

                if (!$this->mysqlDatabase->sendQuery($query)) {
                    throw new InstallException(sprintf(
                        'Drop index "%s" from table "%s" failed! Error: %s',
                        $index->Key_name,
                        $tableName,
                        $this->mysqlDatabase->error()
                    ));
                }
            }

            yield new Success(sprintf('Keys for table "%s" installed!', $tableName));
        }
    }

    private function installKey(string $tableName, Key $key): string
    {
        $name = mb_substr($key->getName() ?? ($key->isUnique() ? 'unique' : '') . implode('', array_map(
            static fn (string $column): string => ucfirst($column),
            $key->getColumns(),
        )), 0, 64);

        if (!$this->mysqlDatabase->execute('SHOW INDEXES FROM `' . $tableName . '` WHERE `Key_name`=?', [$name])) {
            throw new InstallException(sprintf(
                'Get indexes for table "%s" failed! Error: %s',
                $tableName,
                $this->mysqlDatabase->error()
            ));
        }

        $keyFields = $this->mysqlDatabase->fetchAssocList();
        //@todo hier muss geprüft werden ob die richtigen columns im key sind. Er noch/nicht mehr unique ist usw.

        if (count($keyFields) === count($key->getColumns())) {
            return $name;
        }

        $query =
            'CREATE ' . ($key->isUnique() ? 'UNIQUE ' : '') . 'INDEX `' . $name . '` ON `' . $tableName . '` ' .
            '(`' . implode('`, `', $key->getColumns()) . '`)'
        ;
        $this->logger->debug($query);

        if (!$this->mysqlDatabase->sendQuery($query)) {
            throw new InstallException(sprintf(
                'Add key "%s" for table "%s" failed! Error: %s',
                $name,
                $tableName,
                $this->mysqlDatabase->error()
            ));
        }

        return $name;
    }

    public function getPart(): string
    {
        return InstallService::PART_DATABASE;
    }

    public function getPriority(): int
    {
        return 699;
    }
}