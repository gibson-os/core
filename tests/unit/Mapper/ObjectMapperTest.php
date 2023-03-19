<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Mapper;

use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Mock\Dto\Mapper\IntEnum;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\Mock\Dto\Mapper\MapObjectChild;
use GibsonOS\Mock\Dto\Mapper\MapObjectParent;
use GibsonOS\Test\Unit\Core\UnitTest;

class ObjectMapperTest extends UnitTest
{
    private ObjectMapper $objectMapper;

    /**
     * @throws FactoryError
     */
    protected function _before(): void
    {
        $this->objectMapper = new ObjectMapper(
            $this->serviceManager,
            $this->serviceManager->get(ReflectionManager::class)
        );
    }

    /**
     * @dataProvider getTestData
     *
     * @throws \JsonException
     */
    public function testMapToObject(array $properties, string $exception = null): void
    {
        try {
            $object = $this->objectMapper->mapToObject(MapObject::class, $properties);
        } catch (\Throwable $e) {
            if ($exception !== $e::class) {
                throw $e;
            }

            $this->assertEquals($exception, $e::class, $e->getMessage());

            return;
        }

        $this->assertEquals($properties['stringEnumValue'], $object->getStringEnumValue()->value);
        $this->assertEquals($properties['intValue'], $object->getIntValue());
        $this->assertEquals($properties['nullableIntValue'] ?? null, $object->getNullableIntValue());

        $parentObject = $properties['parent'] ?? null;

        if ($parentObject !== null) {
            $parent = $object->getParent();
            $this->assertEquals($parentObject instanceof MapObjectParent ? $parentObject->isDefault() : ($parentObject['default'] ?? true), $parent->isDefault());
            $this->assertEquals($parentObject instanceof MapObjectParent ? $parentObject->getOptions() : ($parentObject['options'] ?? []), $parent->getOptions());
        }

        $childObjects = $object->getChildObjects();
        $testChildObjects = $properties['childObjects'] ?? null;

        if ($testChildObjects === null) {
            return;
        }

        if (is_string($testChildObjects)) {
            $testChildObjects = JsonUtility::decode($testChildObjects);
        }

        foreach ($testChildObjects as $key => $propertyChildObject) {
            $this->assertTrue(isset($childObjects[$key]), 'Child object doesnt exists!');

            if (!isset($childObjects[$key])) {
                continue;
            }

            $childObject = $childObjects[$key];
            $this->assertEquals($propertyChildObject instanceof MapObjectChild ? $propertyChildObject->getStringValue() : $propertyChildObject['stringValue'], $childObject->getStringValue());
            $this->assertEquals($propertyChildObject instanceof MapObjectChild ? $propertyChildObject->getNullableStringValue() : ($propertyChildObject['nullableStringValue'] ?? null), $childObject->getNullableStringValue());
            $this->assertEquals($propertyChildObject instanceof MapObjectChild ? $propertyChildObject->getNullableIntEnumValue()?->value : ($propertyChildObject['nullableIntEnumValue'] ?? null), $childObject->getNullableIntEnumValue()?->value);
        }
    }

    public function getTestData(): array
    {
        return [
            'only defaults' => [['stringEnumValue' => 'ja', 'intValue' => 1]],
            'only defaults with parent' => [
                [
                    'stringEnumValue' => 'ja',
                    'intValue' => 1,
                    'parent' => [],
                ],
            ],
            'only defaults with child' => [
                [
                    'stringEnumValue' => 'ja',
                    'intValue' => 1,
                    'childObjects' => [
                        ['stringValue' => 'Marvin'],
                    ],
                ],
            ],
            'only defaults with json child' => [
                [
                    'stringEnumValue' => 'ja',
                    'intValue' => 1,
                    'childObjects' => '[{"stringValue": "Marvin"}]',
                ],
            ],
            'only defaults with corrupt json child' => [
                [
                    'stringEnumValue' => 'ja',
                    'intValue' => 1,
                    'childObjects' => '{"stringValue": "Marvin"}',
                ],
                \ReflectionException::class,
            ],
            'only defaults with corrupt child' => [
                [
                    'stringEnumValue' => 'ja',
                    'intValue' => 1,
                    'childObjects' => ['stringValue' => 'Marvin'],
                ],
                \ReflectionException::class,
            ],
            'all values' => [
                [
                    'stringEnumValue' => 'nein',
                    'intValue' => 42,
                    'nullableIntValue' => 24,
                    'parent' => [
                        'default' => false,
                        'options' => [
                            'foo' => 'bar',
                            'muh' => 'mah',
                        ],
                    ],
                    'childObjects' => [
                        [
                            'stringValue' => 'Marvin',
                            'nullableIntEnumValue' => 1,
                            'nullableStringValue' => 'Galaxy',
                        ],
                    ],
                ],
            ],
            'all values with null' => [
                [
                    'stringEnumValue' => 'nein',
                    'intValue' => 42,
                    'nullableIntValue' => null,
                    'parent' => [
                        'default' => null,
                        'options' => null,
                    ],
                    'childObjects' => [
                        [
                            'stringValue' => 'Marvin',
                            'nullableIntEnumValue' => null,
                            'nullableStringValue' => null,
                        ],
                    ],
                ],
            ],
            'with non object value' => [['stringEnumValue' => 'ja', 'intValue' => 1, 'foo' => 'bar']],
            'with missing value' => [['stringEnumValue' => 'ja'], \ReflectionException::class],
            'with wrong enum value' => [['stringEnumValue' => 'Trilian', 'intValue' => 1], \ValueError::class],
            'with wrong enum type' => [['stringEnumValue' => ['ja'], 'intValue' => 1], \ReflectionException::class],
            'with int value as string' => [['stringEnumValue' => 'ja', 'intValue' => '1']],
            'with int enum value as string' => [
                [
                    'stringEnumValue' => 'nein',
                    'intValue' => 42,
                    'childObjects' => [
                        [
                            'stringValue' => 'Marvin',
                            'nullableIntEnumValue' => '1',
                        ],
                    ],
                ],
            ],
            'with objects' => [
                [
                    'stringEnumValue' => 'nein',
                    'intValue' => 42,
                    'parent' => new MapObjectParent(false),
                    'childObjects' => [(new MapObjectChild('Marvin'))->setNullableIntEnumValue(IntEnum::DEFINED)],
                ],
            ],
        ];
    }
}
