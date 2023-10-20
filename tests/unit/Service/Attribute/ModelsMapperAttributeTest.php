<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Attribute;

use Codeception\Test\Unit;
use GibsonOS\Core\Attribute\GetMappedModels;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Service\Attribute\ModelFetcherAttribute;
use GibsonOS\Core\Service\Attribute\ModelsFetcherAttribute;
use GibsonOS\Core\Service\Attribute\ModelsMapperAttribute;
use GibsonOS\Core\Service\Attribute\ObjectMapperAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Transformer\AttributeParameterTransformer;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\MapModelParent;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class ModelsMapperAttributeTest extends Unit
{
    use ModelManagerTrait;

    private ModelsMapperAttribute $modelsMapperAttribute;

    private RequestService|ObjectProphecy $requestService;

    private SessionService|ObjectProphecy $sessionService;

    private ObjectProphecy|ModelFetcherAttribute $modelsFetcherAttribute;

    private ObjectMapperAttribute|ObjectProphecy $objectMapperAttribute;

    private ObjectProphecy|AttributeParameterTransformer $attributeParameterTransformer;

    private ModelMapper|ObjectProphecy $modelMapper;

    protected function _before(): void
    {
        $this->loadModelManager();

        $this->modelMapper = $this->prophesize(ModelMapper::class);
        $this->requestService = $this->prophesize(RequestService::class);
        $this->modelsFetcherAttribute = $this->prophesize(ModelsFetcherAttribute::class);
        $this->objectMapperAttribute = $this->prophesize(ObjectMapperAttribute::class);
        $this->attributeParameterTransformer = $this->prophesize(AttributeParameterTransformer::class);

        $this->modelsMapperAttribute = new ModelsMapperAttribute(
            $this->modelMapper->reveal(),
            new ReflectionManager(),
            $this->modelsFetcherAttribute->reveal(),
            $this->objectMapperAttribute->reveal(),
            $this->attributeParameterTransformer->reveal(),
            $this->modelWrapper->reveal(),
        );
    }

    public function testReplaceWrongAttribute(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->expectException(MapperException::class);

        $this->modelsMapperAttribute->replace(
            new GetModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceRequestError(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });
        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => null])
        ;

        $this->assertEquals(
            [],
            $this->modelsMapperAttribute->replace(
                new GetMappedModels(MapModel::class),
                [],
                $reflectionFunction->getParameters()[0],
            ),
        );
    }

    public function testReplaceRequestErrorNullAllowed(): void
    {
        $reflectionFunction = new ReflectionFunction(function (?array $models) { return $models; });
        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => null])
        ;

        $this->assertNull($this->modelsMapperAttribute->replace(
            new GetMappedModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }

    public function testReplaceWrongModel(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->expectException(MapperException::class);

        $this->modelsMapperAttribute->replace(
            new GetMappedModels(MapObject::class),
            [],
            $reflectionFunction->getParameters()[0],
        );
    }

    public function testReplaceNoRequestValue(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => null])
        ;

        $this->assertEquals([], $this->modelsMapperAttribute->replace(
            new GetMappedModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }

    public function testReplaceNoRequestValueNullAllowed(): void
    {
        $reflectionFunction = new ReflectionFunction(function (?array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => null])
        ;

        $this->assertNull($this->modelsMapperAttribute->replace(
            new GetMappedModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }

    public function testReplaceExistingModel(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => [42]])
        ;
        $this->objectMapperAttribute->getParameterFromRequest($reflectionFunction->getParameters()[0])
            ->shouldBeCalledOnce()
            ->willReturn([[
                'id' => 42,
                'stringEnumValue' => 'ja',
                'intValue' => '24',
            ]])
        ;
        $model = new MapModel($this->modelWrapper->reveal());
        $this->modelMapper->setObjectValues($model, [
            'id' => 42,
            'stringEnumValue' => 'ja',
            'intValue' => '24',
        ])
            ->shouldBeCalledOnce()
            ->willReturn($model)
        ;
        $this->modelMapper->mapToObject(MapModelParent::class, [])
            ->shouldBeCalledOnce()
            ->willReturn(new MapModelParent($this->modelWrapper->reveal()))
        ;
        $this->modelsFetcherAttribute->replace(
            new GetModels(MapModel::class, ['id' => 'id']),
            [],
            $reflectionFunction->getParameters()[0],
        )
            ->shouldBeCalledOnce()
            ->willReturn([new MapModel($this->modelWrapper->reveal())])
        ;

        $this->assertInstanceOf(
            MapModel::class,
            $this->modelsMapperAttribute->replace(
                new GetMappedModels(MapModel::class),
                [],
                $reflectionFunction->getParameters()[0],
            )[0],
        );
    }

    public function testReplaceOkNoResults(): void
    {
        $reflectionFunction = new ReflectionFunction(function (array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => 42])
        ;

        $this->assertEquals(
            [],
            $this->modelsMapperAttribute->replace(
                new GetMappedModels(MapModel::class),
                [],
                $reflectionFunction->getParameters()[0],
            ),
        );
    }

    public function testReplaceOkNoResultsNullAllowed(): void
    {
        $reflectionFunction = new ReflectionFunction(function (?array $models) { return $models; });

        $this->attributeParameterTransformer->transform(['id' => 'id'], 'models')
            ->shouldBeCalledOnce()
            ->willReturn(['id' => 42])
        ;

        $this->assertNull($this->modelsMapperAttribute->replace(
            new GetMappedModels(MapModel::class),
            [],
            $reflectionFunction->getParameters()[0],
        ));
    }
}
