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
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Service\Attribute\ModelFetcherAttribute;
use GibsonOS\Core\Service\Attribute\ModelMapperAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Transformer\AttributeParameterTransformer;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\MapModelParent;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class ModelMapperAttributeTest extends Unit
{
    use ModelManagerTrait;

    private ModelMapperAttribute $modelMapperAttribute;

    private RequestService|ObjectProphecy $requestService;

    private AttributeParameterTransformer|ObjectProphecy $attributeParameterTransformer;

    private ModelFetcherAttribute|ObjectProphecy $modelFetcherAttribute;

    private ModelMapper|ObjectProphecy $modelMapper;

    protected function _before(): void
    {
        $this->loadModelManager();

        $this->requestService = $this->prophesize(RequestService::class);
        $this->modelFetcherAttribute = $this->prophesize(ModelFetcherAttribute::class);
        $this->attributeParameterTransformer = $this->prophesize(AttributeParameterTransformer::class);
        $this->modelMapper = $this->prophesize(ModelMapper::class);

        $this->modelMapperAttribute = new ModelMapperAttribute(
            $this->modelMapper->reveal(),
            $this->requestService->reveal(),
            new ReflectionManager(),
            $this->modelFetcherAttribute->reveal(),
            $this->modelWrapper->reveal(),
            $this->attributeParameterTransformer->reveal(),
        );
    }

    public function testReplaceWrongAttribute(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });

        $this->expectException(MapperException::class);

        $this->modelMapperAttribute->replace(
            new GetModel(),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceWrongModel(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapObject $model) { return $model; });

        $this->expectException(MapperException::class);

        $this->modelMapperAttribute->replace(
            new GetMappedModel(),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceNoRecord(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });

        $this->attributeParameterTransformer->transform(['parent'])
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;
        $this->attributeParameterTransformer->transform(['childObjects'])
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;
        $this->modelFetcherAttribute->replace(
            new GetModel(),
            ['stringEnumValue' => 'ja', 'intValue' => 42],
            $reflectionFunction->getParameters()[0],
        )
            ->shouldBeCalledOnce()
            ->willThrow(SelectError::class)
        ;
        $model = new MapModel($this->modelWrapper->reveal());
        $this->modelMapper->setObjectValues($model, [
            'modelWrapper' => $this->modelWrapper->reveal(),
            'id' => null,
            'nullableIntValue' => null,
            'stringEnumValue' => 'ja',
            'intValue' => '42',
            'parentId' => null,
        ])
            ->shouldBeCalledOnce()
            ->willReturn($model)
        ;

        $this->assertInstanceOf(
            MapModel::class,
            $this->modelMapperAttribute->replace(
                new GetMappedModel(),
                ['stringEnumValue' => 'ja', 'intValue' => 42],
                $reflectionFunction->getParameters()[0],
            ),
        );
    }

    public function testReplaceNoRequestValueNullAllowed(): void
    {
        $reflectionFunction = new ReflectionFunction(function (?MapModel $model) { return $model; });

        $this->assertNull($this->modelMapperAttribute->replace(
            new GetMappedModel(),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }

    public function testReplaceNewModel(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });

        $this->attributeParameterTransformer->transform(['parent'])
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;
        $this->attributeParameterTransformer->transform(['childObjects'])
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;
        $model = new MapModel($this->modelWrapper->reveal());
        $this->modelMapper->setObjectValues($model, [
            'modelWrapper' => $this->modelWrapper->reveal(),
            'id' => null,
            'nullableIntValue' => null,
            'stringEnumValue' => 'ja',
            'intValue' => '42',
            'parentId' => null,
        ])
            ->shouldBeCalledOnce()
            ->willReturn($model)
        ;

        $this->assertInstanceOf(
            MapModel::class,
            $this->modelMapperAttribute->replace(
                new GetMappedModel(),
                ['stringEnumValue' => 'ja', 'intValue' => 42],
                $reflectionFunction->getParameters()[0],
            ),
        );
    }

    public function testReplaceNewModelMissingRequiredValues(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });

        $this->modelFetcherAttribute->replace(
            new GetModel(),
            ['stringEnumValue' => 'YES'],
            $reflectionFunction->getParameters()[0],
        )
            ->shouldBeCalledOnce()
            ->willThrow(SelectError::class)
        ;

        $this->expectException(MapperException::class);

        $this->modelMapperAttribute->replace(
            new GetMappedModel(),
            ['stringEnumValue' => 'YES'],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceNewModelWithRequestValues(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });

        $this->modelFetcherAttribute->replace(
            new GetModel(),
            [],
            $reflectionFunction->getParameters()[0],
        )
            ->shouldBeCalledOnce()
            ->willThrow(SelectError::class)
        ;
        $this->requestService->getRequestValue('id')
            ->shouldBeCalledOnce()
            ->willThrow(RequestError::class)
        ;
        $this->requestService->getRequestValue('nullableIntValue')
            ->shouldBeCalledOnce()
            ->willThrow(RequestError::class)
        ;
        $this->requestService->getRequestValue('parentId')
            ->shouldBeCalledOnce()
            ->willThrow(RequestError::class)
        ;
        $this->requestService->getRequestValue('stringEnumValue')
            ->shouldBeCalledTimes(2)
            ->willReturn('YES')
        ;
        $this->requestService->getRequestValue('intValue')
            ->shouldBeCalledTimes(2)
            ->willReturn(42)
        ;
        $this->attributeParameterTransformer->transform(['parent'])
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;
        $this->attributeParameterTransformer->transform(['childObjects'])
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;
        $model = new MapModel($this->modelWrapper->reveal());
        $this->modelMapper->setObjectValues($model, [
            'modelWrapper' => $this->modelWrapper->reveal(),
            'stringEnumValue' => 'YES',
            'intValue' => 42,
        ])
            ->shouldBeCalledOnce()
            ->willReturn($model)
        ;

        $this->assertInstanceOf(
            MapModel::class,
            $this->modelMapperAttribute->replace(
                new GetMappedModel(),
                [],
                $reflectionFunction->getParameters()[0],
            ),
        );
    }

    public function testReplaceExistingModel(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });

        $this->attributeParameterTransformer->transform(['parent'])
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;
        $this->attributeParameterTransformer->transform(['childObjects'])
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;
        $mapModel = (new MapModel($this->modelWrapper->reveal()))
            ->setStringEnumValue(StringEnum::YES)
            ->setIntValue(42)
        ;
        $this->modelFetcherAttribute->replace(
            new GetModel(),
            [],
            $reflectionFunction->getParameters()[0],
        )
            ->shouldBeCalledOnce()
            ->willReturn($mapModel)
        ;

        $this->assertInstanceOf(
            MapModel::class,
            $this->modelMapperAttribute->replace(
                new GetMappedModel(),
                [],
                $reflectionFunction->getParameters()[0],
            ),
        );
    }

    public function testReplaceWithParent(): void
    {
        $reflectionFunction = new ReflectionFunction(function (MapModel $model) { return $model; });

        $this->attributeParameterTransformer->transform(['parent'])
            ->shouldBeCalledOnce()
            ->willReturn([['id' => 42]])
        ;
        $this->attributeParameterTransformer->transform(['childObjects'])
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;
        $mapModel = (new MapModel($this->modelWrapper->reveal()))
            ->setStringEnumValue(StringEnum::YES)
            ->setIntValue(42)
        ;
        $this->modelFetcherAttribute->replace(
            new GetModel(),
            ['stringEnumValue' => 'ja', 'intValue' => 42],
            $reflectionFunction->getParameters()[0],
        )
            ->shouldBeCalledOnce()
            ->willReturn($mapModel)
        ;
        $this->modelMapper->setObjectValues($mapModel, [
            'modelWrapper' => $this->modelWrapper->reveal(),
            'id' => null,
            'nullableIntValue' => null,
            'stringEnumValue' => 'ja',
            'intValue' => 42,
            'parentId' => null,
        ])
            ->shouldBeCalledOnce()
            ->willReturn($mapModel)
        ;
        $mapModelParent = new MapModelParent($this->modelWrapper->reveal());
        $this->modelMapper->mapToObject(MapModelParent::class, ['id' => 42])
            ->shouldBeCalledOnce()
            ->willReturn($mapModelParent)
        ;

        $model = $this->modelMapperAttribute->replace(
            new GetMappedModel(),
            ['stringEnumValue' => 'ja', 'intValue' => 42],
            $reflectionFunction->getParameters()[0],
        );

        $this->assertInstanceOf(MapModel::class, $model);
        $this->assertEquals($mapModelParent, $model->getParent());
    }
}
