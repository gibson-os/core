<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Service\Attribute;

use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Service\Attribute\ModelsFetcherAttribute;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\UnitTest\AbstractTest;
use ReflectionFunction;

class ModelsFetcherAttributeTest extends AbstractTest
{
    private ModelsFetcherAttribute $modelsFetcherAttribute;

    protected function _before(): void
    {
        $this->showFieldsFromMapModel();

        $this->modelsFetcherAttribute = $this->serviceManager->get(ModelsFetcherAttribute::class);
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
            $return,
            $this->modelsFetcherAttribute->replace($attribute, $parameters, $reflectionFunction->getParameters()[0])
        );
    }

    public function getData(): array
    {
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
                    (new MapModel())
                        ->setId(24)
                        ->setStringEnumValue(StringEnum::YES)
                        ->setIntValue(142),
                    (new MapModel())
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
