<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Attribute;

use Codeception\Test\Unit;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\Core\Service\Attribute\ModelsFetcherAttribute;
use GibsonOS\Core\Service\Attribute\ObjectMapperAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class ModelsFetcherAttributeTest extends Unit
{
    use ModelManagerTrait;

    private ModelsFetcherAttribute $modelsFetcherAttribute;

    private RequestService|ObjectProphecy $requestService;

    private SessionService|ObjectProphecy $sessionService;

    protected function _before(): void
    {
        $this->loadModelManager();
        $this->requestService = $this->prophesize(RequestService::class);
        $this->sessionService = $this->prophesize(SessionService::class);
        $reflectionManager = new ReflectionManager();

        $this->modelsFetcherAttribute = new ModelsFetcherAttribute(
            $this->mysqlDatabase->reveal(),
            $this->modelManager->reveal(),
            $reflectionManager,
            $this->sessionService->reveal(),
            new ObjectMapperAttribute(
                new ObjectMapper(
                    new ServiceManager(),
                    $reflectionManager,
                ),
                $this->requestService->reveal(),
                $reflectionManager,
            ),
        );
    }

    /**
     * @dataProvider getData
     */
    public function testReplace(
        GetModels $attribute,
        array $ids,
        array $parameters,
        array $modelsValues,
        callable $function,
        array $return,
    ): void {
        $reflectionFunction = new ReflectionFunction($function);

        foreach ($parameters as $key => $value) {
            $this->requestService->getRequestValue($key)
                ->willReturn($value)
            ;
        }

        if (count($return)) {
            $return[0]->getTableName();
        }

        if (count($ids) > 0) {
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
                    'WHERE (`id`=?) OR (`id`=?)',
                $ids,
            )
                ->shouldBeCalledOnce()
                ->willReturn(true)
            ;
            $this->mysqlDatabase->fetchAssocList()
                ->shouldBeCalledOnce()
                ->willReturn($modelsValues)
            ;
        }

        $this->assertEquals(
            $return,
            $this->modelsFetcherAttribute->replace($attribute, $parameters, $reflectionFunction->getParameters()[0]),
        );
    }

    public function getData(): array
    {
        $modelWrapper = $this->prophesize(ModelWrapper::class);

        return [
            'OK' => [
                new GetModels(MapModel::class),
                [24, 42],
                ['models' => [['id' => 24], ['id' => 42]]],
                [
                    ['id' => 24, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                    ['id' => 42, 'nullable_int_value' => 7, 'string_enum_value' => 'NO', 'int_value' => 124],
                ],
                function (array $models) { return $models; },
                [
                    (new MapModel($modelWrapper->reveal()))
                        ->setId(24)
                        ->setStringEnumValue(StringEnum::YES)
                        ->setIntValue(142),
                    (new MapModel($modelWrapper->reveal()))
                        ->setId(42)
                        ->setStringEnumValue(StringEnum::NO)
                        ->setNullableIntValue(7)
                        ->setIntValue(124),
                ],
            ],
            'Empty' => [
                new GetModels(MapModel::class),
                [24, 42],
                ['models' => [['id' => 24], ['id' => 42]]],
                [],
                function (array $models = []) { return $models; },
                [],
            ],
            'Empty request' => [
                new GetModels(MapModel::class),
                [],
                ['models' => []],
                [
                    ['id' => 24, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                    ['id' => 42, 'nullable_int_value' => 7, 'string_enum_value' => 'NO', 'int_value' => 124],
                ],
                function (array $models = []) { return $models; },
                [],
            ],
        ];
    }
}
