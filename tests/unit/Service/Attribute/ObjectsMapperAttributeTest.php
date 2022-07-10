<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Service\Attribute;

use GibsonOS\Core\Attribute\GetObjects;
use GibsonOS\Core\Service\Attribute\ObjectsMapperAttribute;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\UnitTest\AbstractTest;
use ReflectionFunction;

class ObjectsMapperAttributeTest extends AbstractTest
{
    private ObjectsMapperAttribute $objectsMapperAttribute;

    protected function _before(): void
    {
        $this->objectsMapperAttribute = $this->serviceManager->get(ObjectsMapperAttribute::class);
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
