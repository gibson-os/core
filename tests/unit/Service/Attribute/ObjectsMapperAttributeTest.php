<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Attribute;

use Codeception\Test\Unit;
use GibsonOS\Core\Attribute\GetObjects;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\Core\Service\Attribute\ObjectMapperAttribute;
use GibsonOS\Core\Service\Attribute\ObjectsMapperAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class ObjectsMapperAttributeTest extends Unit
{
    use ProphecyTrait;

    private ObjectsMapperAttribute $objectsMapperAttribute;

    private RequestService|ObjectProphecy $requestService;

    protected function _before(): void
    {
        $this->requestService = $this->prophesize(RequestService::class);
        $reflectionManager = new ReflectionManager();
        $objectMapper = new ObjectMapper(
            new ServiceManager(),
            $reflectionManager,
        );

        $this->objectsMapperAttribute = new ObjectsMapperAttribute(
            new ObjectMapperAttribute(
                $objectMapper,
                $this->requestService->reveal(),
                $reflectionManager,
            ),
            $objectMapper,
        );
    }

    /**
     * @dataProvider getData
     */
    public function testReplace(
        GetObjects $attribute,
        array $parameters,
        callable $function,
        array $return
    ): void {
        $reflectionFunction = new ReflectionFunction($function);

        foreach ($parameters as $key => $value) {
            $this->requestService->getRequestValue($key)
                ->willReturn($value)
            ;
        }

        $this->assertEquals(
            $return,
            $this->objectsMapperAttribute->replace($attribute, $parameters, $reflectionFunction->getParameters()[0]),
        );
    }

    public function getData(): array
    {
        return [
            'OK' => [
                new GetObjects(MapObject::class),
                [
                    'objects' => [
                        ['stringEnumValue' => 'ja', 'intValue' => 42],
                        ['stringEnumValue' => 'nein', 'intValue' => 24],
                    ],
                ],
                function (array $objects) { return $objects; },
                [
                    new MapObject(StringEnum::YES, 42),
                    new MapObject(StringEnum::NO, 24),
                ],
            ],
            'Empty' => [
                new GetObjects(MapObject::class),
                ['objects' => []],
                function (array $objects) { return $objects; },
                [],
            ],
        ];
    }
}
