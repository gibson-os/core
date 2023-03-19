<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Attribute;

use GibsonOS\Core\Attribute\GetObject;
use GibsonOS\Core\Service\Attribute\ObjectMapperAttribute;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use GibsonOS\Test\Unit\Core\UnitTest;

class ObjectMapperAttributeTest extends UnitTest
{
    private ObjectMapperAttribute $objectMapperAttribute;

    protected function _before(): void
    {
        $this->objectMapperAttribute = $this->serviceManager->get(ObjectMapperAttribute::class);
    }

    /**
     * @dataProvider getData
     */
    public function testReplace(
        GetObject $attribute,
        array $parameters,
        callable $function,
        ?MapObject $return
    ): void {
        $reflectionFunction = new \ReflectionFunction($function);

        $this->assertEquals(
            $return,
            $this->objectMapperAttribute->replace($attribute, $parameters, $reflectionFunction->getParameters()[0]),
        );
    }

    public function getData(): array
    {
        return [
            'OK' => [
                new GetObject(),
                ['stringEnumValue' => 'ja', 'intValue' => 42],
                function (MapObject $object) { return $object; },
                new MapObject(StringEnum::YES, 42),
            ],
            'Optional' => [
                new GetObject(),
                [],
                function (?MapObject $object) { return $object; },
                null,
            ],
        ];
    }
}
