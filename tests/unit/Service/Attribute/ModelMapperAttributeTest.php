<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Service\Attribute;

use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Service\Attribute\ModelMapperAttribute;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\UnitTest\AbstractTest;
use ReflectionFunction;

class ModelMapperAttributeTest extends AbstractTest
{
    private ModelMapperAttribute $modelMapperAttribute;

    protected function _before(): void
    {
        $this->showFieldsFromMapModel();

        $this->modelMapperAttribute = $this->serviceManager->get(ModelMapperAttribute::class);
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
        ?MapModel $return
    ): void {
        $reflectionFunction = new ReflectionFunction($function);

        foreach ($parameters as $key => $value) {
            $this->requestService->getRequestValue($key)
//                ->shouldBeCalledTimes(2)
                ->willReturn($value)
            ;
        }

        if ($id !== null) {
            $this->database->execute(
                'SELECT `gibson_o_s_mock_dto_mapper_map_model`.`id`, `gibson_o_s_mock_dto_mapper_map_model`.`nullable_int_value`, `gibson_o_s_mock_dto_mapper_map_model`.`string_enum_value`, `gibson_o_s_mock_dto_mapper_map_model`.`int_value`, `gibson_o_s_mock_dto_mapper_map_model`.`parent_id` ' .
                'FROM `' . $this->databaseName . '`.`gibson_o_s_mock_dto_mapper_map_model` ' .
                'WHERE `id`=? ' .
                'LIMIT 1',
                [$id]
            )
                ->shouldBeCalledOnce()
                ->willReturn(true)
            ;
            $this->database->fetchAssocList()
                ->shouldBeCalledOnce()
                ->willReturn(count($modelValues) ? [$modelValues] : $modelValues)
            ;
        }

        if ($return !== null) {
            $return->getTableName();
        }

        $this->assertEquals(
            json_encode($return),
            json_encode($this->modelMapperAttribute->replace($attribute, $parameters, $reflectionFunction->getParameters()[0]))
        );
    }

    public function getData(): array
    {
        return [
            'OK' => [
                new GetMappedModel(),
                42,
                ['id' => 42],
                ['id' => 42, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                function (MapModel $model) { return $model; },
                (new MapModel())
                    ->setId(42)
                    ->setStringEnumValue(StringEnum::YES)
                    ->setIntValue(142),
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
                (new MapModel())
                    ->setId(42)
                    ->setStringEnumValue(StringEnum::YES)
                    ->setIntValue(142),
            ],
            'Change values' => [
                new GetMappedModel(),
                42,
                ['id' => 42, 'nullableIntValue' => 420, 'stringEnumValue' => 'nein', 'intValue' => 24],
                ['id' => 42, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                function (MapModel $model) { return $model; },
                (new MapModel())
                    ->setId(42)
                    ->setNullableIntValue(420)
                    ->setStringEnumValue(StringEnum::NO)
                    ->setIntValue(24),
            ],
            'New model' => [
                new GetMappedModel(),
                null,
                ['nullableIntValue' => 240, 'stringEnumValue' => 'ja', 'intValue' => 42],
                [],
                function (MapModel $model) { return $model; },
                (new MapModel())
                    ->setNullableIntValue(240)
                    ->setStringEnumValue(StringEnum::YES)
                    ->setIntValue(42),
            ],
        ];
    }
}
