<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Attribute;

use Codeception\Test\Unit;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\Attribute\ModelFetcherAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Mock\Dto\Mapper\MapObjectParent;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use InvalidArgumentException;
use MDO\Dto\Query\Where;
use MDO\Dto\Record;
use MDO\Dto\Result;
use MDO\Dto\Table;
use MDO\Exception\ClientException;
use MDO\Query\SelectQuery;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class ModelFetcherAttributeTest extends Unit
{
    use ModelManagerTrait;

    private ModelFetcherAttribute $modelFetcherAttribute;

    private RequestService|ObjectProphecy $requestService;

    private SessionService|ObjectProphecy $sessionService;

    protected function _before(): void
    {
        $this->requestService = $this->prophesize(RequestService::class);
        $this->sessionService = $this->prophesize(SessionService::class);

        $this->loadModelManager();

        $this->modelFetcherAttribute = new ModelFetcherAttribute(
            $this->tableManager->reveal(),
            $this->modelManager->reveal(),
            $this->requestService->reveal(),
            new ReflectionManager(),
            $this->sessionService->reveal(),
            $this->client->reveal(),
            $this->modelWrapper->reveal(),
        );
    }

    public function testReplaceWrongAttribute(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });

        $this->expectException(MapperException::class);

        $this->modelFetcherAttribute->replace(
            new GetMappedModel(),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceWrongModel(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapObject $model) { return $model; });

        $this->expectException(InvalidArgumentException::class);

        $this->modelFetcherAttribute->replace(
            new GetModel(),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceNoRequestValue(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });

        $this->requestService->getRequestValue('id')
            ->shouldBeCalledOnce()
            ->willThrow(RequestError::class)
        ;

        $this->assertNull($this->modelFetcherAttribute->replace(
            new GetModel(),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }

    public function testReplaceClientException(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });
        $this->requestService->getRequestValue('id')
            ->shouldBeCalledOnce()
            ->willReturn(42)
        ;
        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willThrow(ClientException::class)
        ;

        $this->expectException(SelectError::class);

        $this->modelFetcherAttribute->replace(
            new GetModel(),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceClientExceptionAllowsNull(): void
    {
        $reflectionFunction = new ReflectionFunction(function (?MapModel $model) { return $model; });
        $this->requestService->getRequestValue('id')
            ->shouldBeCalledOnce()
            ->willReturn(42)
        ;
        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willThrow(ClientException::class)
        ;

        $this->assertNull($this->modelFetcherAttribute->replace(
            new GetModel(),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }

    public function testReplaceOk(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });
        $this->requestService->getRequestValue('id')
            ->shouldBeCalledOnce()
            ->willReturn(42)
        ;

        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;
        $record = new Record([]);
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $model = new MapModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $model)
            ->shouldBeCalledOnce()
        ;

        $replacedModel = $this->modelFetcherAttribute->replace(
            new GetModel(),
            [],
            $reflectionFunction->getParameters()[0],
        );

        $this->assertNotEquals($model, $replacedModel);
        $model->getTableName();
        $this->assertEquals($model, $replacedModel);
    }

    public function testReplaceWithValue(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });
        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;
        $record = new Record([]);
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $model = new MapModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $model)
            ->shouldBeCalledOnce()
        ;

        $replacedModel = $this->modelFetcherAttribute->replace(
            new GetModel(['id' => 'value.42']),
            [],
            $reflectionFunction->getParameters()[0],
        );

        $this->assertNotEquals($model, $replacedModel);
        $model->getTableName();
        $this->assertEquals($model, $replacedModel);
    }

    public function testReplaceWithSessionValue(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });
        $this->sessionService->get('marvin')
            ->shouldBeCalledOnce()
            ->willReturn(42)
        ;

        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;
        $record = new Record([]);
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $model = new MapModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $model)
            ->shouldBeCalledOnce()
        ;

        $replacedModel = $this->modelFetcherAttribute->replace(
            new GetModel(['id' => 'session.marvin']),
            [],
            $reflectionFunction->getParameters()[0],
        );

        $this->assertNotEquals($model, $replacedModel);
        $model->getTableName();
        $this->assertEquals($model, $replacedModel);
    }

    public function testReplaceWithNestedSessionValue(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });
        $this->sessionService->get('arthur')
            ->shouldBeCalledOnce()
            ->willReturn(['dent' => 42])
        ;

        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;
        $record = new Record([]);
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $model = new MapModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $model)
            ->shouldBeCalledOnce()
        ;

        $replacedModel = $this->modelFetcherAttribute->replace(
            new GetModel(['id' => 'session.arthur.dent']),
            [],
            $reflectionFunction->getParameters()[0],
        );

        $this->assertNotEquals($model, $replacedModel);
        $model->getTableName();
        $this->assertEquals($model, $replacedModel);
    }

    public function testReplaceWithNestedSessionObjectValue(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });
        $this->sessionService->get('arthur')
            ->shouldBeCalledOnce()
            ->willReturn(['dent' => new MapObject(StringEnum::YES, 42)])
        ;

        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;
        $record = new Record([]);
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $model = new MapModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $model)
            ->shouldBeCalledOnce()
        ;

        $replacedModel = $this->modelFetcherAttribute->replace(
            new GetModel(['id' => 'session.arthur.dent.intValue']),
            [],
            $reflectionFunction->getParameters()[0],
        );

        $this->assertNotEquals($model, $replacedModel);
        $model->getTableName();
        $this->assertEquals($model, $replacedModel);
    }

    public function testReplaceWithNestedSessionObjectArrayValue(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });
        $this->sessionService->get('arthur')
            ->shouldBeCalledOnce()
            ->willReturn(new MapObjectParent(true, ['dent' => 42]))
        ;

        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id`=?', [42]))
            ->setLimit(1)
        ;
        $record = new Record([]);
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;
        $model = new MapModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $model)
            ->shouldBeCalledOnce()
        ;

        $replacedModel = $this->modelFetcherAttribute->replace(
            new GetModel(['id' => 'session.arthur.options.dent']),
            [],
            $reflectionFunction->getParameters()[0],
        );

        $this->assertNotEquals($model, $replacedModel);
        $model->getTableName();
        $this->assertEquals($model, $replacedModel);
    }
}
