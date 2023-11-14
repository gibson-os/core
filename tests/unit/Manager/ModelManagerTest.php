<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Manager;

use Codeception\Test\Unit;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\Attribute\TableNameAttribute;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Mock\Model\MockModel;
use MDO\Client;
use MDO\Dto\Field;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Table;
use MDO\Dto\Value;
use MDO\Enum\Type;
use MDO\Manager\TableManager;
use MDO\Query\DeleteQuery;
use MDO\Query\ReplaceQuery;
use MDO\Service\DeleteService;
use MDO\Service\ReplaceService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ModelManagerTest extends Unit
{
    use ProphecyTrait;

    private ModelManager $modelManager;

    private DateTimeService|ObjectProphecy $dateTimeService;

    private TableManager|ObjectProphecy $tableManager;

    private JsonUtility|ObjectProphecy $jsonUtility;

    private ReplaceService|ObjectProphecy $replaceService;

    private DeleteService|ObjectProphecy $deleteService;

    private Client|ObjectProphecy $client;

    private ModelWrapper|ObjectProphecy $modelWrapper;

    private Table $table;

    protected function _before()
    {
        $this->dateTimeService = $this->prophesize(DateTimeService::class);
        $this->jsonUtility = $this->prophesize(JsonUtility::class);
        $this->tableManager = $this->prophesize(TableManager::class);
        $this->replaceService = $this->prophesize(ReplaceService::class);
        $this->deleteService = $this->prophesize(DeleteService::class);
        $this->client = $this->prophesize(Client::class);
        $this->modelWrapper = $this->prophesize(ModelWrapper::class);
        $reflectionManager = new ReflectionManager();

        $this->client->getDatabaseName()
            ->willReturn('galaxy')
        ;
        $this->table = new Table(
            'marvin',
            [
                new Field('id', false, Type::BIGINT, 'PRI', null, 'auto_increment', 20),
                new Field('parent_id', true, Type::BIGINT, 'MUL', null, ''),
            ],
        );
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledOnce()
            ->willReturn($this->table)
        ;

        $this->modelManager = new ModelManager(
            $this->dateTimeService->reveal(),
            $this->jsonUtility->reveal(),
            $reflectionManager,
            new TableNameAttribute($reflectionManager, $this->modelWrapper->reveal()),
            $this->tableManager->reveal(),
            $this->replaceService->reveal(),
            $this->deleteService->reveal(),
            $this->client->reveal(),
            $this->modelWrapper->reveal(),
        );
    }

    public function testSaveWithoutChildren(): void
    {
        $replaceQuery = new ReplaceQuery($this->table, ['parent_id' => new Value(null)]);
        $record = new Record(['id' => new Value(42), 'parent_id' => new Value(null)]);
        $this->replaceService->replaceAndLoadRecord($replaceQuery)
            ->shouldBeCalledonce()
            ->willReturn($record)
        ;
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(3)
        ;

        $model = new MockModel($this->modelWrapper->reveal());
        $this->modelManager->saveWithoutChildren($model);

        $this->assertEquals(42, $model->getId());
    }

    public function testSaveWithoutChildrenWithSetChildren(): void
    {
        $replaceQuery = new ReplaceQuery($this->table, ['parent_id' => new Value(null)]);
        $record = new Record(['id' => new Value(42), 'parent_id' => new Value(null)]);
        $this->replaceService->replaceAndLoadRecord($replaceQuery)
            ->shouldBeCalledonce()
            ->willReturn($record)
        ;
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(3)
        ;

        $children = new MockModel($this->modelWrapper->reveal());
        $model = (new MockModel($this->modelWrapper->reveal()))
            ->setChildren([$children])
        ;
        $this->modelManager->saveWithoutChildren($model);

        $this->assertEquals(42, $model->getId());
        $this->assertEquals(42, $children->getParentId());
        $this->assertEquals($model, $children->getParent());
    }

    public function testSaveWithoutChildrenWithAddChildren(): void
    {
        $replaceQuery = new ReplaceQuery($this->table, ['parent_id' => new Value(null)]);
        $record = new Record(['id' => new Value(42), 'parent_id' => new Value(null)]);
        $this->replaceService->replaceAndLoadRecord($replaceQuery)
            ->shouldBeCalledonce()
            ->willReturn($record)
        ;
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(3)
        ;

        $children = new MockModel($this->modelWrapper->reveal());
        $model = (new MockModel($this->modelWrapper->reveal()))
            ->addChildren([$children])
        ;
        $this->modelManager->saveWithoutChildren($model);

        $this->assertEquals(42, $model->getId());
        $this->assertEquals(42, $children->getParentId());
        $this->assertEquals($model, $children->getParent());
    }

    public function testSave(): void
    {
        $this->client->isTransaction()
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;
        $this->client->startTransaction()
            ->shouldBeCalledOnce()
        ;
        $replaceQuery = new ReplaceQuery($this->table, ['parent_id' => new Value(null)]);
        $record = new Record(['id' => new Value(42), 'parent_id' => new Value(null)]);
        $this->replaceService->replaceAndLoadRecord($replaceQuery)
            ->shouldBeCalledonce()
            ->willReturn($record)
        ;
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(4)
        ;
        $deleteQuery = (new DeleteQuery($this->table))->addWhere(new Where('`parent_id`=?', [42]));
        $this->client->execute($deleteQuery);
        $this->client->commit()
            ->shouldBeCalledOnce()
        ;

        $model = new MockModel($this->modelWrapper->reveal());
        $this->modelManager->save($model);

        $this->assertEquals(42, $model->getId());
    }

    public function testSaveWithSetChildren(): void
    {
        $this->client->isTransaction()
            ->shouldBeCalledTimes(2)
            ->willReturn(false, true)
        ;
        $this->client->startTransaction()
            ->shouldBeCalledOnce()
        ;
        $record = new Record(['id' => new Value(42), 'parent_id' => new Value(null)]);
        $this->replaceService->replaceAndLoadRecord(new ReplaceQuery($this->table, ['parent_id' => new Value(null)]))
            ->shouldBeCalledonce()
            ->willReturn($record)
        ;
        $childRecord = new Record(['id' => new Value(24), 'parent_id' => new Value(42)]);
        $this->replaceService->replaceAndLoadRecord(new ReplaceQuery($this->table, ['parent_id' => new Value(42)]))
            ->shouldBeCalledonce()
            ->willReturn($childRecord)
        ;
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(8)
        ;
        $this->client->execute((new DeleteQuery($this->table))->addWhere(new Where('`parent_id`=?', [24])));
        $this->client->execute(
            (new DeleteQuery($this->table))
            ->addWhere(new Where('`parent_id`=?', [42]))
            ->addWhere(new Where('`id`!=?', [24])),
        );
        $this->client->commit()
            ->shouldBeCalledOnce()
        ;

        $children = new MockModel($this->modelWrapper->reveal());
        $model = (new MockModel($this->modelWrapper->reveal()))
            ->setChildren([$children])
        ;
        $this->modelManager->save($model);

        $this->assertEquals(42, $model->getId());
    }

    public function testSaveWithAddChildren(): void
    {
        $this->client->isTransaction()
            ->shouldBeCalledTimes(2)
            ->willReturn(false, true)
        ;
        $this->client->startTransaction()
            ->shouldBeCalledOnce()
        ;
        $record = new Record(['id' => new Value(42), 'parent_id' => new Value(null)]);
        $this->replaceService->replaceAndLoadRecord(new ReplaceQuery($this->table, ['parent_id' => new Value(null)]))
            ->shouldBeCalledonce()
            ->willReturn($record)
        ;
        $childRecord = new Record(['id' => new Value(24), 'parent_id' => new Value(42)]);
        $this->replaceService->replaceAndLoadRecord(new ReplaceQuery($this->table, ['parent_id' => new Value(42)]))
            ->shouldBeCalledonce()
            ->willReturn($childRecord)
        ;
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(8)
        ;
        $this->client->execute((new DeleteQuery($this->table))->addWhere(new Where('`parent_id`=?', [24])));
        $this->client->execute(
            (new DeleteQuery($this->table))
                ->addWhere(new Where('`parent_id`=?', [42]))
                ->addWhere(new Where('`id`!=?', [24])),
        );
        $this->client->commit()
            ->shouldBeCalledOnce()
        ;

        $children = new MockModel($this->modelWrapper->reveal());
        $model = (new MockModel($this->modelWrapper->reveal()))
            ->addChildren([$children])
        ;
        $this->modelManager->save($model);

        $this->assertEquals(42, $model->getId());
    }
}
