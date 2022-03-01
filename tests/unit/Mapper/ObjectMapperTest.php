<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Mapper;

use GibsonOS\Core\Attribute\ObjectMapper as ObjectMapperAttribute;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\UnitTest\AbstractTest;
use JsonException;
use ReflectionException;

class ObjectMapperTest extends AbstractTest
{
    private ObjectMapper $objectMapper;

    protected function _before()
    {
        $this->objectMapper = new ObjectMapper(
            $this->serviceManagerService,
            $this->serviceManagerService->get(ReflectionManager::class)
        );
    }

    /**
     * @dataProvider getTestData
     *
     * @throws FactoryError
     * @throws MapperException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testMapToObject(array $properties): void
    {
        $object = $this->objectMapper->mapToObject(mapObject::class, $properties);

        $this->assertEquals($properties['stringEnumValue'], $object->getStringEnumValue()->value);
        $this->assertEquals($properties['intValue'], $object->getIntValue());
        $this->assertEquals($properties['nullableIntValue'] ?? null, $object->getNullableIntValue());
        $childObjects = $object->getChildObjects();

        foreach ($properties['childObjects'] ?? [] as $key => $propertyChildObject) {
            $this->assertTrue(isset($childObjects[$key]), 'Child object doesnt exists!');

            if (!isset($childObjects[$key])) {
                continue;
            }

            $childObject = $childObjects[$key];
            var_dump($childObject);
            $this->assertEquals($propertyChildObject['stringValue'], $childObject->getStringValue());
            $this->assertEquals($propertyChildObject['nullableStringValue'], $childObject->getNullableStringValue());
            $this->assertEquals($propertyChildObject['nullableIntEnumValue'], $childObject->getNullableIntEnumValue()?->value);
        }
    }

    public function getTestData(): array
    {
        return [
            'only defaults' => [['stringEnumValue' => 'ja', 'intValue' => 1]],
            'only defaults with child' => [['stringEnumValue' => 'ja', 'intValue' => 1, 'childObjects' => [['stringValue' => 'Marvin']]]],
            'only defaults with json child' => [['stringEnumValue' => 'ja', 'intValue' => 1, 'childObjects' => '[{"stringValue": "Marvin"}]']],
        ];
    }
}

enum stringEnum: string
{
    case NO = 'nein';
    case YES = 'ja';
}

enum intEnum: int
{
    case false = 0;
    case true = 1;
}

class mapObject
{
    /**
     * @var mapChildObject[]
     */
    #[ObjectMapperAttribute(mapChildObject::class)]
    private array $childObjects = [];

    private ?int $nullableIntValue = null;

    public function __construct(
        private stringEnum $stringEnumValue,
        private int $intValue
    ) {
    }

    /**
     * @return mapChildObject[]
     */
    public function getChildObjects(): array
    {
        return $this->childObjects;
    }

    /**
     * @param mapChildObject[] $childObjects
     */
    public function setChildObjects(array $childObjects): mapObject
    {
        $this->childObjects = $childObjects;

        return $this;
    }

    public function getNullableIntValue(): ?int
    {
        return $this->nullableIntValue;
    }

    public function setNullableIntValue(?int $nullableIntValue): mapObject
    {
        $this->nullableIntValue = $nullableIntValue;

        return $this;
    }

    public function getStringEnumValue(): stringEnum
    {
        return $this->stringEnumValue;
    }

    public function getIntValue(): int
    {
        return $this->intValue;
    }
}

class mapChildObject
{
    private ?intEnum $nullableIntEnumValue = null;

    private ?string $nullableStringValue = null;

    public function __construct(private string $stringValue)
    {
    }

    public function getNullableIntEnumValue(): ?intEnum
    {
        return $this->nullableIntEnumValue;
    }

    public function setNullableIntEnumValue(?intEnum $nullableIntEnumValue): mapChildObject
    {
        $this->nullableIntEnumValue = $nullableIntEnumValue;

        return $this;
    }

    public function getNullableStringValue(): ?string
    {
        return $this->nullableStringValue;
    }

    public function setNullableStringValue(?string $nullableStringValue): mapChildObject
    {
        $this->nullableStringValue = $nullableStringValue;

        return $this;
    }

    public function getStringValue(): string
    {
        return $this->stringValue;
    }
}
