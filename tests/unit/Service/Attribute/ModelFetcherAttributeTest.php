<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Attribute;

use Codeception\Test\Unit;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Service\Attribute\ModelFetcherAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use mysqlDatabase;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class ModelFetcherAttributeTest extends Unit
{
    use ModelManagerTrait;

    private ModelFetcherAttribute $modelFetcherAttribute;

    private RequestService|ObjectProphecy $requestService;

    private SessionService|ObjectProphecy $sessionService;

    protected function _before(): void
    {
        $this->requestService = $this->prophesize(RequestService::class);
        $this->sessionService = $this->prophesize(SessionService::class);

        $this->loadModelManager();
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

        $this->modelFetcherAttribute = new ModelFetcherAttribute(
            $this->mysqlDatabase->reveal(),
            $this->modelManager->reveal(),
            $this->requestService->reveal(),
            new ReflectionManager(),
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

        $this->mysqlDatabase->execute(
            'SELECT `gibson_o_s_mock_dto_mapper_map_model`.`id`, `gibson_o_s_mock_dto_mapper_map_model`.`nullable_int_value`, `gibson_o_s_mock_dto_mapper_map_model`.`string_enum_value`, `gibson_o_s_mock_dto_mapper_map_model`.`int_value`, `gibson_o_s_mock_dto_mapper_map_model`.`parent_id` ' .
            'FROM `galaxy`.`gibson_o_s_mock_dto_mapper_map_model` ' .
            'WHERE `id`=? ' .
            'LIMIT 1',
            [$id]
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchAssocList()
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
            ),
        );
    }

    public function getData(): array
    {
        $mysqlDatabase = $this->prophesize(mysqlDatabase::class);

        return [
            'OK' => [
                new GetModel(),
                42,
                ['id' => 42],
                ['id' => 42, 'nullable_int_value' => null, 'string_enum_value' => 'YES', 'int_value' => 142],
                function (MapModel $model) { return $model; },
                (new MapModel($mysqlDatabase->reveal()))
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
                (new MapModel($mysqlDatabase->reveal()))
                    ->setId(42)
                    ->setStringEnumValue(StringEnum::YES)
                    ->setIntValue(142),
            ],
        ];
    }
}
