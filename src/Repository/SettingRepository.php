<?php
declare(strict_types=1);

namespace GibsonOS\Core\Repository;

use GibsonOS\Core\Attribute\GetTable;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use JsonException;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class SettingRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Setting::class)]
        private readonly string $settingTableName,
        #[GetTable(Module::class)]
        private readonly Table $moduleTable,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     *
     * @return Setting[]
     */
    public function getAll(int $moduleId, ?int $userId): array
    {
        $parameters = [$moduleId];

        if ($userId !== null) {
            $parameters[] = $userId;
        }

        return $this->fetchAll(
            '`module_id`=? AND (`user_id` IS NULL' . ($userId === null ? '' : ' OR `user_id`=?') . ')',
            $parameters,
            Setting::class,
        );
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     *
     * @return Setting[]
     */
    public function getAllByModuleName(string $moduleName, ?int $userId): array
    {
        $selectQuery = $this->getSelectQuery($this->settingTableName, 's')
            ->addJoin(new Join($this->moduleTable, 'm', '`s`.`module_id`=`m`.`id`'))
            ->addWhere(new Where('`m`.`name`=?', [$moduleName]))
        ;
        $where = new Where('`s`.`user_id` IS NULL', []);

        if ($userId !== null) {
            $where = new Where('`s`.`user_id` IS NULL OR `s`.`user_id`=?', [$userId]);
        }

        $selectQuery->addWhere($where);

        return $this->getModels($selectQuery, Setting::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws RecordException
     */
    public function getByKey(int $moduleId, ?int $userId, string $key): Setting
    {
        $parameters = [$moduleId];

        if ($userId !== null) {
            $parameters[] = $userId;
        }

        $parameters[] = $key;

        return $this->fetchOne(
            '`module_id`=? AND ' .
            '(`user_id` IS NULL' . ($userId === null ? '' : ' OR `user_id`=?') . ') AND ' .
            '`key`=?',
            $parameters,
            Setting::class,
        );
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getByKeyAndValue(int $moduleId, string $key, string $value): Setting
    {
        return $this->fetchOne(
            '`module_id`=? AND `key`=? AND `value`=?',
            [$moduleId, $key, $value],
            Setting::class,
        );
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getByKeyValueAndModuleName(string $moduleName, string $key, string $value): Setting
    {
        $selectQuery = $this->getSelectQuery($this->settingTableName, 's')
            ->addJoin(new Join($this->moduleTable, 'm', '`s`.`module_id`=`m`.`id`'))
            ->addWhere(new Where('`m`.`name`=?', [$moduleName]))
            ->addWhere(new Where('`m`.`key`=?', [$key]))
            ->addWhere(new Where('`m`.`value`=?', [$value]))
            ->setLimit(1)
        ;

        return $this->getModel($selectQuery, Setting::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return Setting[]
     */
    public function getAllByKey(int $moduleId, string $key): array
    {
        return $this->fetchAll(
            '`module_id`=? AND `key`=?',
            [$moduleId, $key],
            Setting::class,
        );
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return Setting[]
     */
    public function getAllByKeyAndModuleName(string $moduleName, string $key): array
    {
        $selectQuery = $this->getSelectQuery($this->settingTableName, 's')
            ->addJoin(new Join($this->moduleTable, 'm', '`s`.`module_id`= `m`.`id`'))
            ->addWhere(new Where('`m`.`name`=?', [$moduleName]))
            ->addWhere(new Where('`s`.`key`=?', [$key]))
        ;

        return $this->getModels($selectQuery, Setting::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    public function getByKeyAndModuleName(string $moduleName, ?int $userId, string $key): Setting
    {
        $selectQuery = $this->getSelectQuery($this->settingTableName, 's')
            ->addJoin(new Join($this->moduleTable, 'm', '`s`.`module_id`=`m`.`id`'))
            ->addWhere(new Where('`m`.`name`=?', [$moduleName]))
            ->addWhere(new Where('`s`.`key`=?', [$key]))
            ->setOrder('`s`.`user_id`', OrderDirection::DESC)
            ->setLimit(1)
        ;
        $where = new Where('`s`.`user_id` IS NULL', []);

        if ($userId !== null) {
            $where = new Where('`s`.`user_id` IS NULL OR `s`.`user_id`=?', [$userId]);
        }

        $selectQuery->addWhere($where);

        return $this->getModel($selectQuery, Setting::class);
    }
}
