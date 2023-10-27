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
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Service\Attribute\TableNameAttribute;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use MDO\Client;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class KeyInstall extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManager $serviceManagerService,
        private readonly Client $client,
        private readonly TableNameAttribute $tableAttribute,
        private readonly ReflectionManager $reflectionManager,
    ) {
        parent::__construct($serviceManagerService);
    }

    /**
     * @throws InstallException
     * @throws FactoryError
     * @throws GetError
     * @throws ReflectionException
     * @throws RecordException
     */
    public function install(string $module): Generator
    {
        $path = $this->dirService->addEndSlash($module) . 'src' . DIRECTORY_SEPARATOR . 'Model';

        foreach ($this->getFiles($path) as $file) {
            $className = $this->serviceManagerService->getNamespaceByPath($file);
            $reflectionClass = $this->reflectionManager->getReflectionClass($className);

            if (!$this->reflectionManager->hasAttribute($reflectionClass, Table::class)) {
                continue;
            }

            $installedKeys = ['PRIMARY'];
            /** @var AbstractModel $model */
            $model = new $className($this->modelWrapper);
            $tableName = $model->getTableName();
            $keyAttributes = $this->reflectionManager->getAttributes($reflectionClass, Key::class);

            foreach ($keyAttributes as $keyAttribute) {
                if (count($keyAttribute->getColumns()) === 0) {
                    throw new InstallException(sprintf(
                        'Key attribute for model "%s" has no columns defined!',
                        $className,
                    ));
                }

                $installedKeys[] = $this->installKey($tableName, $keyAttribute);
            }

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                foreach ($this->reflectionManager->getAttributes($reflectionProperty, Key::class) as $keyAttribute) {
                    if (count($keyAttribute->getColumns()) !== 0) {
                        throw new InstallException(sprintf(
                            'Key attribute for property "%s::%s" is not empty!',
                            $className,
                            $reflectionProperty->getName(),
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
                ') AND `Seq_in_index`=1'
            ;
            $parameters = array_merge(
                [$this->envService->getString('MYSQL_DATABASE'), $tableName],
                $installedKeys,
            );

            try {
                $result = $this->client->execute($query, $parameters);
            } catch (ClientException) {
                throw new InstallException(sprintf(
                    'Show indexes from table "%s" failed! Error: %s',
                    $tableName,
                    $this->client->getError(),
                ));
            }

            foreach ($result->iterateRecords() as $index) {
                $keyName = (string) $index->get('Key_name')->getValue();
                $query = sprintf('DROP INDEX `%s` ON `%s`', $keyName, $tableName);
                $this->logger->debug($query);

                try {
                    $this->client->execute($query);
                } catch (ClientException) {
                    throw new InstallException(sprintf(
                        'Drop index "%s" from table "%s" failed! Error: %s',
                        $keyName,
                        $tableName,
                        $this->client->getError(),
                    ));
                }
            }

            yield new Success(sprintf('Keys for table "%s" installed!', $tableName));
        }
    }

    private function installKey(string $tableName, Key $key): string
    {
        $name = \mb_substr($key->getName() ?? ($key->isUnique() ? 'unique' : '') . implode('', array_map(
            static fn (string $column): string => ucfirst($column),
            $key->getColumns(),
        )), 0, 64);

        try {
            $result = $this->client->execute(sprintf('SHOW INDEXES FROM `%s` WHERE `Key_name`=?', $tableName), [$name]);
        } catch (ClientException) {
            throw new InstallException(sprintf(
                'Get indexes for table "%s" failed! Error: %s',
                $tableName,
                $this->client->getError(),
            ));
        }

        $keyFields = iterator_to_array($result->iterateRecords());
        // @todo hier muss geprüft werden ob die richtigen columns im key sind. Er noch/nicht mehr unique ist usw.

        if (count($keyFields) === count($key->getColumns())) {
            return $name;
        }

        $query = sprintf(
            'CREATE %sINDEX `%s` ON `%s` (`%s`)',
            $key->isUnique() ? 'UNIQUE ' : '',
            $name,
            $tableName,
            implode('`, `', $key->getColumns()),
        );
        $this->logger->debug($query);

        try {
            $this->client->execute($query);
        } catch (ClientException) {
            throw new InstallException(sprintf(
                'Add key "%s" for table "%s" failed! Error: %s',
                $name,
                $tableName,
                $this->client->getError(),
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
