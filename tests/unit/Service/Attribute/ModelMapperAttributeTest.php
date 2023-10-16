<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Attribute;

use Codeception\Test\Unit;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Service\Attribute\ModelFetcherAttribute;
use GibsonOS\Core\Service\Attribute\ModelMapperAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class ModelMapperAttributeTest extends Unit
{
    use ModelManagerTrait;

    private ModelMapperAttribute $modelMapperAttribute;

    private RequestService|ObjectProphecy $requestService;

    private SessionService|ObjectProphecy $sessionService;

    protected function _before(): void
    {
        $this->requestService = $this->prophesize(RequestService::class);
        $this->sessionService = $this->prophesize(SessionService::class);

        $this->loadModelManager();

        $reflectionManager = new ReflectionManager();

        $this->modelMapperAttribute = new ModelMapperAttribute(
            new ModelMapper(new ServiceManager(), $reflectionManager),
            $this->requestService->reveal(),
            $reflectionManager,
            new ModelFetcherAttribute(
                $this->mysqlDatabase->reveal(),
                $this->modelManager->reveal(),
                $this->requestService->reveal(),
                $reflectionManager,
                $this->sessionService->reveal(),
            ),
            $this->sessionService->reveal(),
        );
    }

    /**
     * @dataProvider getData
     */
    public function testReplace(
        GetMappedModel $attribute,
        ?int $id,
        array $parameters,
        array $modelValues,
        callable $function,
        ?MapModel $return,
    ): void {
        $this->requestService->getRequestValue(Argument::any())->willThrow(RequestError::class);
        $reflectionFunction = new ReflectionFunction($function);

        foreach ($parameters as $key => $value) {
            $this->requestService->getRequestValue($key)
                ->willReturn($value)
            ;
        }

        if ($id !== null) {
            $this->mysqlDatabase->getDatabaseName()
                ->shouldBeCalledOnce()
                ->willReturn('galaxy')
            ;
            $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `galaxy`.`gibson_o_s_mock_dto_mapper_map_model`')
                ->shouldBeCalledOnce()
                ->willReturn(true)
            ;
            $this->mysqlDatabase->fetchRow()
                ->shouldBeCalledTimes(6)
                ->willReturn(
                    ['id', 'bigint(20) unsigned', 'NO', 'PRI', null, 'auto_increment'],
                    ['nullable_int_value', 'bigint(20)', 'YES', '', null, ''],
                    ['string_enum_value', 'enum(\'NO\', \'YES\')', 'NO', '', null, ''],
                    ['int_value', 'bigint(20)', 'NO', '', null, ''],
                    ['parent_id', 'bigint(20) unsigned', 'YES', '', null, ''],
                    null,
                )
            ;

            $this->mysqlDatabase->execute(
                'SELECT `gibson_o_s_mock_dto_mapper_map_model`.`id`, `gibson_o_s_mock_dto_mapper_map_model`.`nullable_int_value`, `gibson_o_s_mock_dto_mapper_map_model`.`string_enum_value`, `gibson_o_s_mock_dto_mapper_map_model`.`int_value`, `gibson_o_s_mock_dto_mapper_map_model`.`parent_id` ' .
                'FROM `galaxy`.`gibson_o_s_mock_dto_mapper_map_model` ' .
                'WHERE `id`=? ' .
                'LIMIT 1',
                [$id],
            )
                ->shouldBeCalledOnce()
                ->willReturn(true)
            ;
            $this->mysqlDatabase->fetchAssocList()
                ->shouldBeCalledOnce()
                ->willReturn(count($modelValues) ? [$modelValues] : $modelValues)
            ;
        }

        if ($return !== null) {
            $return->getTableName();
        }

        $this->assertEquals(
            json_encode($return),
            json_encode($this->modelMapperAttribute->replace($attribute, $parameters, $reflectionFunction->getParameters()[0])),
        );
    }

    public function getData(): array
    {
        $modelWrapper = $this->prophesize(ModelWrapper::class);

        return [
            'OK' => [
                new GetMappedModel(),
                42,
                ['id' => 42],
                ['id' => 42, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                function (MapModel $model) { return $model; },
                (new MapModel($modelWrapper->reveal()))
                    ->setId(42)
                    ->setStringEnumValue(StringEnum::YES)
                    ->setIntValue(142)
                    ->setChildObjects([]),
            ],
            'Optional Parameter' => [
                new GetMappedModel(),
                42,
                ['id' => 42],
                [],
                function (?MapModel $model) { return $model; },
                null,
            ],
            'Changed parameter' => [
                new GetMappedModel(['id' => 'modelId']),
                42,
                ['modelId' => 42],
                ['id' => 42, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                function (MapModel $model) { return $model; },
                (new MapModel($modelWrapper->reveal()))
                    ->setId(42)
                    ->setStringEnumValue(StringEnum::YES)
                    ->setIntValue(142)
                    ->setChildObjects([]),
            ],
            'Change values' => [
                new GetMappedModel(),
                42,
                ['id' => 42, 'nullableIntValue' => 420, 'stringEnumValue' => 'nein', 'intValue' => 24],
                ['id' => 42, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                function (MapModel $model) { return $model; },
                (new MapModel($modelWrapper->reveal()))
                    ->setId(42)
                    ->setNullableIntValue(420)
                    ->setStringEnumValue(StringEnum::NO)
                    ->setIntValue(24)
                    ->setChildObjects([]),
            ],
            'New model' => [
                new GetMappedModel(),
                null,
                ['nullableIntValue' => 240, 'stringEnumValue' => 'ja', 'intValue' => 42],
                [],
                function (MapModel $model) { return $model; },
                (new MapModel($modelWrapper->reveal()))
                    ->setNullableIntValue(240)
                    ->setStringEnumValue(StringEnum::YES)
                    ->setIntValue(42)
                    ->setChildObjects([]),
            ],
        ];
    }
}
