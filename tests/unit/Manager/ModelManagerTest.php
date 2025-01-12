<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Manager;

use Codeception\Test\Unit;
use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Dto\Violation;
use GibsonOS\Core\Exception\ViolationException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Query\ChildrenQuery;
use GibsonOS\Core\Service\Attribute\TableNameAttribute;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\ValidatorService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Validator\AbstractValidator;
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

    private ChildrenQuery|ObjectProphecy $childrenQuery;

    private ValidatorService|ObjectProphecy $validatorService;

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
        $this->childrenQuery = $this->prophesize(ChildrenQuery::class);
        $this->validatorService = $this->prophesize(ValidatorService::class);
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
            $this->childrenQuery->reveal(),
            $this->validatorService->reveal(),
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
        $this->validatorService->validate($model)
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;

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
        $this->validatorService->validate($model)
            ->shouldBeCalledOnce()
            ->willReturn([])
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
        $this->validatorService->validate($model)
            ->shouldBeCalledOnce()
            ->willReturn([])
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
            ->shouldBeCalledTimes(3)
        ;

        $model = new MockModel($this->modelWrapper->reveal());
        $this->validatorService->validate($model)
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;

        $deleteQuery = (new DeleteQuery($this->table))->addWhere(new Where('`parent_id`=?', [42]));
        $this->childrenQuery->getDeleteQuery(
            $model,
            't',
            new ChildrenMapping('children', 'child_', 'c'),
        )
            ->shouldBeCalledOnce()
            ->willReturn($deleteQuery)
        ;
        $this->client->execute($deleteQuery);
        $this->client->commit()
            ->shouldBeCalledOnce()
        ;

        $this->modelManager->save($model);

        $this->assertEquals(42, $model->getId());
    }

    public function testSaveValidationError(): void
    {
        $this->client->isTransaction()
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;
        $this->client->startTransaction()
            ->shouldBeCalledOnce()
        ;
        $this->tableManager->getTable('marvin')
            ->shouldNotBeCalled()
        ;

        $model = new MockModel($this->modelWrapper->reveal());
        $this->validatorService->validate($model)
            ->shouldBeCalledOnce()
            ->willReturn([new Violation(
                'no hope',
                $this->prophesize(AbstractValidator::class)->reveal(),
                $model::class,
            )])
        ;

        $this->expectException(ViolationException::class);
        $this->expectExceptionMessage($model::class . ': no hope');

        $this->modelManager->save($model);
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
            ->shouldBeCalledTimes(6)
        ;

        $children = new MockModel($this->modelWrapper->reveal());
        $this->validatorService->validate($children)
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $model = (new MockModel($this->modelWrapper->reveal()))
            ->setChildren([$children])
        ;
        $this->validatorService->validate($model)
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;

        $deleteQuery = (new DeleteQuery($this->table))->addWhere(new Where('`parent_id`=?', [24]));
        $this->childrenQuery->getDeleteQuery(
            $model,
            't',
            new ChildrenMapping('children', 'child_', 'c'),
        )
            ->shouldBeCalledOnce()
            ->willReturn($deleteQuery)
        ;
        $this->client->execute($deleteQuery)
            ->shouldBeCalledOnce()
        ;
        $deleteQuery2 = (new DeleteQuery($this->table))
            ->addWhere(new Where('`parent_id`=?', [42]))
            ->addWhere(new Where('`id`!=?', [24]))
        ;
        $this->childrenQuery->getDeleteQuery(
            $children,
            't',
            new ChildrenMapping('children', 'child_', 'c'),
        )
            ->shouldBeCalledOnce()
            ->willReturn($deleteQuery2)
        ;
        $this->client->execute($deleteQuery2)
            ->shouldBeCalledOnce()
        ;
        $this->client->commit()
            ->shouldBeCalledOnce()
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
            ->shouldBeCalledTimes(6)
        ;

        $children = new MockModel($this->modelWrapper->reveal());
        $this->validatorService->validate($children)
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $model = (new MockModel($this->modelWrapper->reveal()))
            ->addChildren([$children])
        ;
        $this->validatorService->validate($model)
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;

        $deleteQuery = (new DeleteQuery($this->table))->addWhere(new Where('`parent_id`=?', [24]));
        $this->childrenQuery->getDeleteQuery(
            $model,
            't',
            new ChildrenMapping('children', 'child_', 'c'),
        )
            ->shouldBeCalledOnce()
            ->willReturn($deleteQuery)
        ;
        $this->client->execute($deleteQuery);
        $deleteQuery2 = (new DeleteQuery($this->table))
            ->addWhere(new Where('`parent_id`=?', [42]))
            ->addWhere(new Where('`id`!=?', [24]))
        ;
        $this->childrenQuery->getDeleteQuery(
            $children,
            't',
            new ChildrenMapping('children', 'child_', 'c'),
        )
            ->shouldBeCalledOnce()
            ->willReturn($deleteQuery2)
        ;
        $this->client->execute($deleteQuery2)
            ->shouldBeCalledOnce()
        ;
        $this->client->commit()
            ->shouldBeCalledOnce()
        ;

        $this->modelManager->save($model);

        $this->assertEquals(42, $model->getId());
    }
}
