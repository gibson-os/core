<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Attribute;

use Codeception\Test\Unit;
use GibsonOS\Core\Attribute\GetMappedModels;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Service\Attribute\ModelsFetcherAttribute;
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
use MDO\Service\SelectService;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class ModelsFetcherAttributeTest extends Unit
{
    use ModelManagerTrait;

    private ModelsFetcherAttribute $modelsFetcherAttribute;

    private RequestService|ObjectProphecy $requestService;

    private SessionService|ObjectProphecy $sessionService;

    private ObjectProphecy|SelectService $selectService;

    private ObjectProphecy|AttributeParameterTransformer $attributeParameterTransformer;

    protected function _before(): void
    {
        $this->loadModelManager();
        $this->requestService = $this->prophesize(RequestService::class);
        $this->sessionService = $this->prophesize(SessionService::class);
        $this->attributeParameterTransformer = $this->prophesize(AttributeParameterTransformer::class);
        $this->selectService = $this->prophesize(SelectService::class);

        $this->modelsFetcherAttribute = new ModelsFetcherAttribute(
            $this->client->reveal(),
            $this->tableManager->reveal(),
            $this->modelManager->reveal(),
            $this->modelWrapper->reveal(),
            $this->attributeParameterTransformer->reveal(),
            $this->selectService->reveal(),
        );
    }

    public function testReplaceWrongAttribute(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->expectException(MapperException::class);

        $this->modelsFetcherAttribute->replace(
            new GetMappedModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceRequestError(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });
        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models.')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => null])
        ;

        $this->assertEquals(
            [],
            $this->modelsFetcherAttribute->replace(
                new GetModels(MapModel::class),
                [],
                $reflectionFunction->getParameters()[0],
            ),
        );
    }

    public function testReplaceRequestErrorNullAllowed(): void
    {
        $reflectionFunction = new ReflectionFunction(function (?array $models) { return $models; });
        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models.')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => null])
        ;

        $this->assertNull($this->modelsFetcherAttribute->replace(
            new GetModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }

    public function testReplaceWrongModel(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->expectException(MapperException::class);

        $this->modelsFetcherAttribute->replace(
            new GetModels(MapObject::class),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceNoRequestValue(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models.')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => null])
        ;

        $this->assertEquals([], $this->modelsFetcherAttribute->replace(
            new GetModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }

    public function testReplaceNoRequestValueNullAllowed(): void
    {
        $reflectionFunction = new ReflectionFunction(function (?array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models.')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => null])
        ;

        $this->assertNull($this->modelsFetcherAttribute->replace(
            new GetModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }

    public function testReplaceOk(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models.')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => 42])
        ;
        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $this->selectService->getParametersString([42])
            ->shouldBeCalledOnce()
            ->willReturn('?')
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id` IN (?)', [42]))
        ;
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([new Record([])])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;

        $this->assertInstanceOf(
            MapModel::class,
            $this->modelsFetcherAttribute->replace(
                new GetModels(MapModel::class),
                [],
                $reflectionFunction->getParameters()[0],
            )[0],
        );
    }

    public function testReplaceOkNoResults(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models.')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => 42])
        ;
        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $this->selectService->getParametersString([42])
            ->shouldBeCalledOnce()
            ->willReturn('?')
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id` IN (?)', [42]))
        ;
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;

        $this->assertEquals(
            [],
            $this->modelsFetcherAttribute->replace(
                new GetModels(MapModel::class),
                [],
                $reflectionFunction->getParameters()[0],
            ),
        );
    }

    public function testReplaceOkNoResultsNullAllowed(): void
    {
        $reflectionFunction = new ReflectionFunction(function (?array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models.')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => 42])
        ;
        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $this->selectService->getParametersString([42])
            ->shouldBeCalledOnce()
            ->willReturn('?')
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id` IN (?)', [42]))
        ;
        $result = $this->prophesize(Result::class);
        $result->iterateRecords()
            ->shouldBeCalledOnce()
            ->willYield([])
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willReturn($result->reveal())
        ;

        $this->assertNull($this->modelsFetcherAttribute->replace(
            new GetModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }

    public function testReplaceClientException(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models.')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => 42])
        ;
        $table = new Table('gibson_o_s_mock_dto_mapper_map_model', []);
        $this->tableManager->getTable('gibson_o_s_mock_dto_mapper_map_model')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;
        $this->selectService->getParametersString([42])
            ->shouldBeCalledOnce()
            ->willReturn('?')
        ;
        $selectQuery = (new SelectQuery($table))
            ->addWhere(new Where('`id` IN (?)', [42]))
        ;
        $this->client->execute($selectQuery)
            ->shouldBeCalledOnce()
            ->willThrow(ClientException::class)
        ;
        $this->client->getError()
            ->shouldBeCalledOnce()
            ->willReturn('no hope')
        ;

        $this->expectException(SelectError::class);

        $this->modelsFetcherAttribute->replace(
            new GetModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }
}
