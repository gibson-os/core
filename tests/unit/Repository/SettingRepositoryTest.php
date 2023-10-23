<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Repository\SettingRepository;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Enum\OrderDirection;
use MDO\Query\SelectQuery;

class SettingRepositoryTest extends Unit
{
    use RepositoryTrait;

    private SettingRepository $settingRepository;

    private Table $moduleTable;

    protected function _before()
    {
        $this->loadRepository('setting');
        $this->moduleTable = new Table('module', []);

        $this->settingRepository = new SettingRepository(
            $this->repositoryWrapper->reveal(),
            $this->table->getTableName(),
            'module',
        );
    }

    public function testGetAll(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`module_id`=? AND (`user_id` IS NULL OR `user_id`=?)', [42, 24]))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Setting::class, ''),
            $this->settingRepository->getAll(42, 24)[0],
        );
    }

    public function testGetAllUserIdEmpty(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('`module_id`=? AND (`user_id` IS NULL)', [42]))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Setting::class, ''),
            $this->settingRepository->getAll(42, null)[0],
        );
    }

    public function testGetAllByModuleName(): void
    {
        $selectQuery = (new SelectQuery($this->table, 's'))
            ->addJoin(new Join($this->moduleTable, 'm', '`s`.`module_id`=`m`.`id`'))
            ->addWhere(new Where('`m`.`name`=?', ['marvin']))
            ->addWhere(new Where('`s`.`user_id` IS NULL OR `s`.`user_id`=?', [42]))
        ;

        $model = $this->loadModel($selectQuery, Setting::class, '');
        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledOnce()
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledTimes(2)
        ;
        $this->tableManager->getTable($this->moduleTable->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->moduleTable)
        ;

        $this->assertEquals($model, $this->settingRepository->getAllByModuleName('marvin', 42)[0]);
    }

    public function testGetAllByModuleNameEmptyUserId(): void
    {
        $selectQuery = (new SelectQuery($this->table, 's'))
            ->addJoin(new Join($this->moduleTable, 'm', '`s`.`module_id`=`m`.`id`'))
            ->addWhere(new Where('`m`.`name`=?', ['marvin']))
            ->addWhere(new Where('`s`.`user_id` IS NULL', []))
        ;

        $model = $this->loadModel($selectQuery, Setting::class, '');
        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledOnce()
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledTimes(2)
        ;
        $this->tableManager->getTable($this->moduleTable->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->moduleTable)
        ;

        $this->assertEquals($model, $this->settingRepository->getAllByModuleName('marvin', null)[0]);
    }

    public function testGetByKey(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                '`module_id`=? AND (`user_id` IS NULL OR `user_id`=?) AND `key`=?',
                [42, 24, 'marvin'],
            ))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Setting::class),
            $this->settingRepository->getByKey(42, 24, 'marvin'),
        );
    }

    public function testGetByKeyEmptyUserId(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where(
                '`module_id`=? AND (`user_id` IS NULL) AND `key`=?',
                [42, 'marvin'],
            ))
            ->setLimit(1)
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, Setting::class),
            $this->settingRepository->getByKey(42, null, 'marvin'),
        );
    }

    public function testGetByKeyAndModuleName(): void
    {
        $selectQuery = (new SelectQuery($this->table, 's'))
            ->addJoin(new Join($this->moduleTable, 'm', '`s`.`module_id`=`m`.`id`'))
            ->addWhere(new Where('`m`.`name`=?', ['galaxy']))
            ->addWhere(new Where('`s`.`key`=?', ['marvin']))
            ->addWhere(new Where('`s`.`user_id` IS NULL OR `s`.`user_id`=?', [42]))
            ->setOrder('`s`.`user_id`', OrderDirection::DESC)
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Setting::class);
        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledOnce()
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledTimes(2)
        ;
        $this->tableManager->getTable($this->moduleTable->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->moduleTable)
        ;

        $this->assertEquals(
            $model,
            $this->settingRepository->getByKeyAndModuleName('galaxy', 42, 'marvin'),
        );
    }

    public function testGetByKeyAndModuleNameEmptyUserId(): void
    {
        $selectQuery = (new SelectQuery($this->table, 's'))
            ->addJoin(new Join($this->moduleTable, 'm', '`s`.`module_id`=`m`.`id`'))
            ->addWhere(new Where('`m`.`name`=?', ['galaxy']))
            ->addWhere(new Where('`s`.`key`=?', ['marvin']))
            ->addWhere(new Where('`s`.`user_id` IS NULL', []))
            ->setOrder('`s`.`user_id`', OrderDirection::DESC)
            ->setLimit(1)
        ;

        $model = $this->loadModel($selectQuery, Setting::class);
        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledOnce()
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledTimes(2)
        ;
        $this->tableManager->getTable($this->moduleTable->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->moduleTable)
        ;

        $this->assertEquals(
            $model,
            $this->settingRepository->getByKeyAndModuleName('galaxy', null, 'marvin'),
        );
    }
}
