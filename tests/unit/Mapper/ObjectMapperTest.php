<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Mapper;

use Exception;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ObjectMapper;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Mock\Dto\Mapper\MapObject;
use GibsonOS\UnitTest\AbstractTest;
use JsonException;
use ReflectionException;
use Throwable;
use ValueError;

class ObjectMapperTest extends AbstractTest
{
    private ObjectMapper $objectMapper;

    /**
     * @throws FactoryError
     */
    protected function _before(): void
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
    public function testMapToObject(array $properties, string $exception = null): void
    {
        try {
            $object = $this->objectMapper->mapToObject(MapObject::class, $properties);
        } catch (Throwable $e) {
            if ($exception !== $e::class) {
                throw new Exception($e::class . ': ' . $e->getMessage(), $e->getCode(), $e);
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
            $this->assertEquals($parentObject['default'] ?? true, $parent->isDefault());
            $this->assertEquals($parentObject['options'] ?? [], $parent->getOptions());
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
            $this->assertEquals($propertyChildObject['stringValue'], $childObject->getStringValue());
            $this->assertEquals($propertyChildObject['nullableStringValue'] ?? null, $childObject->getNullableStringValue());
            $this->assertEquals($propertyChildObject['nullableIntEnumValue'] ?? null, $childObject->getNullableIntEnumValue()?->value);
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
                ReflectionException::class,
            ],
            'only defaults with corrupt child' => [
                [
                    'stringEnumValue' => 'ja',
                    'intValue' => 1,
                    'childObjects' => ['stringValue' => 'Marvin'],
                ],
                ReflectionException::class,
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
            'with missing value' => [['stringEnumValue' => 'ja'], ReflectionException::class],
            'with wrong enum value' => [['stringEnumValue' => 'Trilian', 'intValue' => 1], ValueError::class],
            'with wrong enum type' => [['stringEnumValue' => ['ja'], 'intValue' => 1], ReflectionException::class],
        ];
    }
}
