<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Attribute;

use Codeception\Test\Unit;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\Model\ChildrenMapper;
use GibsonOS\Core\Query\ChildrenQuery;
use GibsonOS\Core\Service\Attribute\ModelFetcherAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Transformer\AttributeParameterTransformer;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
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

    private AttributeParameterTransformer|ObjectProphecy $attributeParameterTransformer;

    private ObjectProphecy|ChildrenQuery $childrenQuery;

    private ObjectProphecy|ChildrenMapper $childrenMapper;

    protected function _before(): void
    {
        $this->loadModelManager();

        $this->attributeParameterTransformer = $this->prophesize(AttributeParameterTransformer::class);
        $this->childrenQuery = $this->prophesize(ChildrenQuery::class);
        $this->childrenMapper = $this->prophesize(ChildrenMapper::class);

        $this->modelFetcherAttribute = new ModelFetcherAttribute(
            $this->tableManager->reveal(),
            $this->modelManager->reveal(),
            new ReflectionManager(),
            $this->client->reveal(),
            $this->modelWrapper->reveal(),
            $this->attributeParameterTransformer->reveal(),
            $this->childrenQuery->reveal(),
            $this->childrenMapper->reveal(),
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

        $this->expectException(MapperException::class);

        $this->modelFetcherAttribute->replace(
            new GetModel(),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceNoRequestValue(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });

        $this->attributeParameterTransformer->transform(['id' => 'id'])
            ->shouldBeCalledOnce()
            ->willReturn(['id' => null])
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
        $this->attributeParameterTransformer->transform(['id' => 'id'])
            ->shouldBeCalledOnce()
            ->willReturn(['id' => 42])
        ;
        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table, 't'))
            ->addWhere(new Where('`t`.`id`=?', [42]))
        ;
        $this->childrenQuery->extend($selectQuery, MapModel::class, [])
            ->shouldBeCalledOnce()
            ->willReturn($selectQuery)
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
        $this->attributeParameterTransformer->transform(['id' => 'id'])
            ->shouldBeCalledOnce()
            ->willReturn(['id' => 42])
        ;
        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table, 't'))
            ->addWhere(new Where('`t`.`id`=?', [42]))
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willThrow(ClientException::class)
        ;
        $this->childrenQuery->extend($selectQuery, MapModel::class, [])
            ->shouldBeCalledOnce()
            ->willReturn($selectQuery)
        ;
        $this->childrenQuery->extend($selectQuery, MapModel::class, [])
            ->shouldBeCalledOnce()
            ->willReturn($selectQuery)
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
        $this->attributeParameterTransformer->transform(['id' => 'id'])
            ->shouldBeCalledOnce()
            ->willReturn(['id' => 42])
        ;

        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $selectQuery = (new SelectQuery($table, 't'))
            ->addWhere(new Where('`t`.`id`=?', [42]))
        ;
        $record = new Record([]);
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([$record])
        ;
        $this->childrenQuery->extend($selectQuery, MapModel::class, [])
            ->shouldBeCalledOnce()
            ->willReturn($selectQuery)
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
}
