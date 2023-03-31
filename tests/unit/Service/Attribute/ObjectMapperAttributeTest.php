<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service\Attribute;

use Codeception\Test\Unit;
use GibsonOS\Core\Attribute\GetObject;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\Core\Service\Attribute\ObjectMapperAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Mock\Dto\Mapper\StringEnum;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionFunction;

class ObjectMapperAttributeTest extends Unit
{
    use ProphecyTrait;

    private ObjectMapperAttribute $objectMapperAttribute;

    private RequestService|ObjectProphecy $requestService;

    protected function _before(): void
    {
        $this->requestService = $this->prophesize(RequestService::class);
        $reflectionManager = new ReflectionManager();

        $this->objectMapperAttribute = new ObjectMapperAttribute(
            new ObjectMapper(
                new ServiceManager(),
                $reflectionManager,
            ),
            $this->requestService->reveal(),
            $reflectionManager,
        );
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
        $reflectionFunction = new ReflectionFunction($function);

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
