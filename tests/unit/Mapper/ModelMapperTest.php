<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Mapper;

use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Mock\Dto\Mapper\IntEnum;
use GibsonOS\Mock\Dto\Mapper\MapModel;
use GibsonOS\Mock\Dto\Mapper\MapModelChild;
use GibsonOS\Mock\Dto\Mapper\MapModelParent;
use GibsonOS\Test\Unit\Core\UnitTest;

class ModelMapperTest extends UnitTest
{
    private ModelMapper $modelMapper;

    protected function _before(): void
    {
        $this->modelMapper = new ModelMapper(
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
            $object = $this->modelMapper->mapToObject(MapModel::class, $properties);
        } catch (\Throwable $e) {
            if ($exception !== $e::class) {
                throw $e;
            }

            $this->assertEquals($exception, $e::class, $e->getMessage());

            return;
        }

        $this->assertEquals($properties['id'] ?? null, $object->getId());
        $this->assertEquals($properties['stringEnumValue'], $object->getStringEnumValue()->value);

        if (isset($properties['intValue'])) {
            $this->assertEquals($properties['intValue'], $object->getIntValue());
        }

        $this->assertEquals($properties['nullableIntValue'] ?? null, $object->getNullableIntValue());
        $parentObject = $properties['parent'] ?? null;
        $this->assertEquals($parentObject instanceof MapModelParent ? $parentObject->getId() : ($parentObject === null ? ($properties['parentId'] ?? null) : ($parentObject['id'] ?? null)), $object->getParentId());

        if ($parentObject !== null) {
            $parent = $object->getParent();
            $this->assertEquals($parentObject instanceof MapModelParent ? $parentObject->getId() : ($parentObject['id'] ?? null), $parent->getId());
            $this->assertEquals($parentObject instanceof MapModelParent ? $parentObject->isDefault() : ($parentObject['default'] ?? false), $parent->isDefault());
            $this->assertEquals($parentObject instanceof MapModelParent ? $parentObject->getOptions() : ($parentObject['options'] ?? []), $parent->getOptions());
//            $this->assertEquals($object, $parent->getObjects()[0]);
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
            $this->assertEquals($propertyChildObject instanceof MapModelChild ? $propertyChildObject->getId() : ($propertyChildObject['id'] ?? null), $childObject->getId());

            if (!$propertyChildObject instanceof MapModelChild && isset($propertyChildObject['stringValue'])) {
                $this->assertEquals($propertyChildObject['stringValue'], $childObject->getStringValue());
            }

            $this->assertEquals($propertyChildObject instanceof MapModelChild ? $propertyChildObject->getNullableStringValue() : ($propertyChildObject['nullableStringValue'] ?? null), $childObject->getNullableStringValue());
            $this->assertEquals($propertyChildObject instanceof MapModelChild ? $propertyChildObject->getNullableIntEnumValue()?->value : ($propertyChildObject['nullableIntEnumValue'] ?? null), $childObject->getNullableIntEnumValue()?->value);

            if (isset($properties['id'])) {
                $this->assertEquals($object->getId(), $childObject->getMapModelId());
            }

            $this->assertEquals($object, $childObject->getMapModel());
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
            ],
            'only defaults with corrupt child' => [
                [
                    'stringEnumValue' => 'ja',
                    'intValue' => 1,
                    'childObjects' => ['stringValue' => 'Marvin'],
                ],
            ],
            'all values' => [
                [
                    'id' => 4242,
                    'stringEnumValue' => 'nein',
                    'intValue' => 42,
                    'nullableIntValue' => 24,
                    'parent' => [
                        'id' => 242,
                        'default' => false,
                        'options' => [
                            'foo' => 'bar',
                            'muh' => 'mah',
                        ],
                    ],
                    'childObjects' => [
                        [
                            'id' => 424242,
                            'stringValue' => 'Marvin',
                            'nullableIntEnumValue' => 1,
                            'nullableStringValue' => 'Galaxy',
                        ],
                    ],
                ],
            ],
            'all values with null' => [
                [
                    'id' => null,
                    'stringEnumValue' => 'nein',
                    'intValue' => 42,
                    'nullableIntValue' => null,
                    'parent' => [
                        'id' => null,
                        'default' => null,
                        'options' => null,
                    ],
                    'childObjects' => [
                        [
                            'id' => null,
                            'stringValue' => 'Marvin',
                            'nullableIntEnumValue' => null,
                            'nullableStringValue' => null,
                        ],
                    ],
                ],
            ],
            'with non object value' => [['stringEnumValue' => 'ja', 'intValue' => 1, 'foo' => 'bar']],
            'with missing value' => [['stringEnumValue' => 'ja']],
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
                    'parent' => (new MapModelParent())->setOptions(['foo' => 'bar']),
                    'childObjects' => [(new MapModelChild())->setNullableIntEnumValue(IntEnum::DEFINED)],
                ],
            ],
            'with parent object id' => [
                [
                    'stringEnumValue' => 'nein',
                    'intValue' => 42,
                    'parentId' => 24,
                ],
            ],
        ];
    }
}
