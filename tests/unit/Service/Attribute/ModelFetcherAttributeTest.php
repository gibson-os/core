<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Service\Attribute;

use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\Attribute\ModelFetcherAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\UnitTest\AbstractTest;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class ModelFetcherAttributeTest extends AbstractTest
{
    use ProphecyTrait;

    private ModelFetcherAttribute $modelFetcherAttribute;

    private ObjectProphecy|RequestService $requestService;

    private ObjectProphecy|SessionService $sessionService;

    protected function _before()
    {
        $this->requestService = $this->prophesize(RequestService::class);
        $this->sessionService = $this->prophesize(SessionService::class);

        $this->showFieldsFromMapModel();

        $this->modelFetcherAttribute = new ModelFetcherAttribute(
            $this->database->reveal(),
            $this->requestService->reveal(),
            $this->serviceManager->get(ReflectionManager::class),
            $this->sessionService->reveal(),
        );
    }

    /**
     * @dataProvider getData
     */
    public function testReplace(
        GetModel $attribute,
        ?int $id,
        array $parameters,
        array $modelValues,
        callable $function,
        ?MapModel $return
    ): void {
        $reflectionFunction = new ReflectionFunction($function);

        foreach ($parameters as $key => $value) {
            $this->requestService->getRequestValue($key)
                ->shouldBeCalledOnce()
                ->willReturn($value)
            ;
        }

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

        if ($return !== null) {
            $return->getTableName();
        }

        $this->assertEquals(
            $return,
            $this->modelFetcherAttribute->replace(
                $attribute,
                $parameters,
                $reflectionFunction->getParameters()[0]
            )
        );
    }

    public function getData(): array
    {
        return [
            'OK' => [
                new GetModel(),
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
                new GetModel(),
                42,
                ['id' => 42],
                [],
                function (?MapModel $model) { return $model; },
                null,
            ],
            'Changed parameter' => [
                new GetModel(['id' => 'modelId']),
                42,
                ['modelId' => 42],
                ['id' => 42, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                function (MapModel $model) { return $model; },
                (new MapModel())
                    ->setId(42)
                    ->setStringEnumValue(StringEnum::YES)
                    ->setIntValue(142),
            ],
        ];
    }
}
