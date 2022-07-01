<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Service\Attribute;

use GibsonOS\Core\Attribute\GetMappedModels;
use GibsonOS\Core\Service\Attribute\ModelsMapperAttribute;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\MapModelChild;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\UnitTest\AbstractTest;
use ReflectionFunction;

class ModelsMapperAttributeTest extends AbstractTest
{
    private ModelsMapperAttribute $modelsMapperAttribute;

    protected function _before(): void
    {
        $this->modelsMapperAttribute = $this->serviceManager->get(ModelsMapperAttribute::class);
    }

    /**
     * @dataProvider getData
     */
    public function testReplace(
        GetMappedModels $attribute,
        array $ids,
        array $parameters,
        array $modelsValues,
        callable $function,
        array $return
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
            $this->database->execute(
                'SELECT `gibson_o_s_mock_dto_mapper_map_model`.`id`, `gibson_o_s_mock_dto_mapper_map_model`.`nullable_int_value`, `gibson_o_s_mock_dto_mapper_map_model`.`string_enum_value`, `gibson_o_s_mock_dto_mapper_map_model`.`int_value`, `gibson_o_s_mock_dto_mapper_map_model`.`parent_id` ' .
                'FROM `galaxy`.`gibson_o_s_mock_dto_mapper_map_model` ' .
                'WHERE (`id`=?) OR (`id`=?)',
                $ids
            )
                ->shouldBeCalledOnce()
                ->willReturn(true)
            ;
            $this->database->fetchAssocList()
                ->shouldBeCalledOnce()
                ->willReturn($modelsValues)
            ;
        }

        $this->assertEquals(
            json_encode($return),
            json_encode($this->modelsMapperAttribute->replace($attribute, $parameters, $reflectionFunction->getParameters()[0]))
        );
    }

    public function getData(): array
    {
        return [
            'OK' => [
                new GetMappedModels(MapModel::class),
                [24, 42],
                ['models' => [['id' => 24], ['id' => 42]]],
                [
                    ['id' => 24, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                    ['id' => 42, 'nullable_int_value' => 7, 'string_enum_value' => 'NO', 'int_value' => 124],
                ],
                function (array $models) { return $models; },
                [
                    (new MapModel())
                        ->setId(24)
                        ->setStringEnumValue(StringEnum::YES)
                        ->setIntValue(142)
                        ->setChildObjects([]),
                    (new MapModel())
                        ->setId(42)
                        ->setStringEnumValue(StringEnum::NO)
                        ->setNullableIntValue(7)
                        ->setIntValue(124)
                        ->setChildObjects([]),
                ],
            ],
            'New' => [
                new GetMappedModels(MapModel::class),
                [24, 42],
                [
                    'models' => [
                        ['id' => 24, 'stringEnumValue' => 'ja', 'intValue' => 142],
                        ['id' => 42, 'stringEnumValue' => 'nein', 'intValue' => 421, 'nullableIntValue' => 7],
                    ],
                ],
                [],
                function (array $models = []) { return $models; },
                [
                    (new MapModel())
                        ->setId(24)
                        ->setStringEnumValue(StringEnum::YES)
                        ->setIntValue(142)
                        ->setChildObjects([]),
                    (new MapModel())
                        ->setId(42)
                        ->setStringEnumValue(StringEnum::NO)
                        ->setNullableIntValue(7)
                        ->setIntValue(421)
                        ->setChildObjects([]),
                ],
            ],
            'Empty request' => [
                new GetMappedModels(MapModel::class),
                [],
                ['models' => []],
                [
                    ['id' => 24, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                    ['id' => 42, 'nullable_int_value' => 7, 'string_enum_value' => 'NO', 'int_value' => 124],
                ],
                function (array $models = []) { return $models; },
                [],
            ],
            'Change' => [
                new GetMappedModels(MapModel::class),
                [24, 42],
                [
                    'models' => [
                        ['id' => 24, 'stringEnumValue' => 'ja', 'intValue' => 142],
                        ['id' => 42, 'stringEnumValue' => 'nein', 'intValue' => 421, 'nullableIntValue' => null],
                    ],
                ],
                [
                    ['id' => 24, 'nullable_int_value' => 9, 'string_enum_value' => 'NO', 'int_value' => 222],
                    ['id' => 42, 'nullable_int_value' => 7, 'string_enum_value' => 'YES', 'int_value' => 111],
                ],
                function (array $models = []) { return $models; },
                [
                    (new MapModel())
                        ->setId(24)
                        ->setStringEnumValue(StringEnum::YES)
                        ->setNullableIntValue(9)
                        ->setIntValue(142)
                        ->setChildObjects([]),
                    (new MapModel())
                        ->setId(42)
                        ->setStringEnumValue(StringEnum::NO)
                        ->setIntValue(421)
                        ->setChildObjects([]),
                ],
            ],
            'With parent models' => [
                new GetMappedModels(MapModel::class),
                [24, 42],
                [
                    'models' => [
                        ['id' => 24, 'stringEnumValue' => 'ja', 'intValue' => 142, 'parent' => ['id' => 42]],
                        ['id' => 42, 'stringEnumValue' => 'nein', 'intValue' => 421, 'nullableIntValue' => 7, 'parent' => []],
                    ],
                ],
                [
                    ['id' => 24, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                    ['id' => 42, 'nullable_int_value' => 7, 'string_enum_value' => 'NO', 'int_value' => 124],
                ],
                function (array $models = []) { return $models; },
                [
                    (new MapModel())
                        ->setId(24)
                        ->setStringEnumValue(StringEnum::YES)
                        ->setIntValue(142)
                        ->setParentId(42)
                        ->setChildObjects([]),
                    (new MapModel())
                        ->setId(42)
                        ->setStringEnumValue(StringEnum::NO)
                        ->setNullableIntValue(7)
                        ->setIntValue(421)
                        ->setChildObjects([]),
                ],
            ],
            'With child models' => [
                new GetMappedModels(MapModel::class),
                [24, 42],
                [
                    'models' => [
                        ['id' => 24, 'stringEnumValue' => 'ja', 'intValue' => 142, 'childObjects' => [['id' => 42], ['id' => 7]]],
                        ['id' => 42, 'stringEnumValue' => 'nein', 'intValue' => 421, 'nullableIntValue' => 7, 'children' => []],
                    ],
                ],
                [
                    ['id' => 24, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                    ['id' => 42, 'nullable_int_value' => 7, 'string_enum_value' => 'NO', 'int_value' => 124],
                ],
                function (array $models = []) { return $models; },
                [
                    (new MapModel())
                        ->setId(24)
                        ->setStringEnumValue(StringEnum::YES)
                        ->setIntValue(142)
                        ->setChildObjects([
                            (new MapModelChild())->setId(42),
                            (new MapModelChild())->setId(7),
                        ]),
                    (new MapModel())
                        ->setId(42)
                        ->setStringEnumValue(StringEnum::NO)
                        ->setNullableIntValue(7)
                        ->setIntValue(421)
                        ->setChildObjects([]),
                ],
            ],
        ];
    }
}
