<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Mapper\Model;

use Codeception\Test\Unit;
use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Mapper\Model\ChildrenMapper;
use GibsonOS\Mock\Model\MockModel;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use MDO\Dto\Field;
use MDO\Dto\Record;
use MDO\Dto\Table;
use MDO\Dto\Value;
use MDO\Enum\Type;
use MDO\Extractor\PrimaryKeyExtractor;
use Prophecy\Prophecy\ObjectProphecy;

class ChildrenMapperTest extends Unit
{
    use ModelManagerTrait;

    private ObjectProphecy|PrimaryKeyExtractor $primaryKeyExtractor;

    private ChildrenMapper $childrenMapper;

    protected function _before()
    {
        $this->loadModelManager();

        $this->primaryKeyExtractor = $this->prophesize(PrimaryKeyExtractor::class);

        $this->childrenMapper = new ChildrenMapper(
            $this->modelManager->reveal(),
            new ReflectionManager(),
            $this->tableManager->reveal(),
            $this->modelWrapper->reveal(),
            $this->primaryKeyExtractor->reveal(),
        );
    }

    public function testGetChildrenModels(): void
    {
        $record = new Record([
            'parent_id' => new Value(42),
            'parent_parent_id' => new Value(420),
            'child_id' => new Value(24),
            'child_parent_id' => new Value(240),
        ]);
        $model = new MockModel($this->modelWrapper->reveal());
        $table = new Table('marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);

        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(2)
            ->willReturn($table)
        ;
        $this->primaryKeyExtractor->extractFromRecord($table, $record, 'parent_')
            ->shouldBeCalledOnce()
            ->willReturn([42])
        ;
        $this->primaryKeyExtractor->extractFromRecord($table, $record, 'child_')
            ->shouldBeCalledOnce()
            ->willReturn([24])
        ;
        $parent = new MockModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $parent, 'parent_')
            ->shouldBeCalledOnce()
        ;
        $parent->getTableName();
        $children = new MockModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $children, 'child_')
            ->shouldBeCalledOnce()
        ;
        $children->getTableName();
        $children->setParent($model);

        $this->childrenMapper->getChildrenModels(
            $record,
            $model,
            [
                new ChildrenMapping('parent', 'parent_', 'p'),
                new ChildrenMapping('children', 'child_', 'c'),
            ],
        );

        $this->assertEquals($parent, $model->getParent());
        $this->assertEquals($children, $model->getChildren()[0]);
    }

    public function testGetChildrenModelsEmpty(): void
    {
        $record = new Record([
            'parent_id' => new Value(null),
            'parent_parent_id' => new Value(null),
            'child_id' => new Value(null),
            'child_parent_id' => new Value(null),
        ]);
        $model = new MockModel($this->modelWrapper->reveal());
        $table = new Table('marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);

        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(2)
            ->willReturn($table)
        ;
        $this->primaryKeyExtractor->extractFromRecord($table, $record, 'parent_')
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;
        $this->primaryKeyExtractor->extractFromRecord($table, $record, 'child_')
            ->shouldBeCalledOnce()
            ->willReturn([null])
        ;

        $this->childrenMapper->getChildrenModels(
            $record,
            $model,
            [
                new ChildrenMapping('parent', 'parent_', 'p'),
                new ChildrenMapping('children', 'child_', 'c'),
            ],
        );

        $this->assertNull($model->getParent());
        $this->assertEquals([], $model->getChildren());
    }

    public function testGetChildrenModelsNested(): void
    {
        $record = new Record([
            'parent_id' => new Value(42),
            'parent_parent_id' => new Value(420),
            'parent_child_id' => new Value(4200),
            'parent_child_parent_id' => new Value(42),
            'child_id' => new Value(24),
            'child_parent_id' => new Value(240),
            'child_child_id' => new Value(2400),
            'child_child_parent_id' => new Value(24),
        ]);
        $model = new MockModel($this->modelWrapper->reveal());
        $table = new Table('marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);

        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(4)
            ->willReturn($table)
        ;
        $this->primaryKeyExtractor->extractFromRecord($table, $record, 'parent_')
            ->shouldBeCalledOnce()
            ->willReturn([42])
        ;
        $this->primaryKeyExtractor->extractFromRecord($table, $record, 'parent_child_')
            ->shouldBeCalledOnce()
            ->willReturn([4200])
        ;
        $this->primaryKeyExtractor->extractFromRecord($table, $record, 'child_')
            ->shouldBeCalledOnce()
            ->willReturn([24])
        ;
        $this->primaryKeyExtractor->extractFromRecord($table, $record, 'child_child_')
            ->shouldBeCalledOnce()
            ->willReturn([2400])
        ;
        $parent = new MockModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $parent, 'parent_')
            ->shouldBeCalledOnce()
        ;
        $parent->getTableName();
        $parentChildren = new MockModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $parentChildren, 'parent_child_')
            ->shouldBeCalledOnce()
        ;
        $parentChildren->getTableName();
        $parent->setChildren([$parentChildren]);
        $children = new MockModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $children, 'child_')
            ->shouldBeCalledOnce()
        ;
        $children->getTableName();
        $children->setParent($model);
        $childrenChildren = new MockModel($this->modelWrapper->reveal());
        $this->modelManager->loadFromRecord($record, $childrenChildren, 'child_child_')
            ->shouldBeCalledOnce()
        ;
        $childrenChildren->getTableName();
        $children->setChildren([$childrenChildren]);

        $this->childrenMapper->getChildrenModels(
            $record,
            $model,
            [
                new ChildrenMapping('parent', 'parent_', 'p', [
                    new ChildrenMapping('children', 'parent_child_', 'pc'),
                ]),
                new ChildrenMapping('children', 'child_', 'c', [
                    new ChildrenMapping('children', 'child_child_', 'cc'),
                ]),
            ],
        );

        $this->assertEquals($parent, $model->getParent());
        $this->assertEquals($parentChildren, $model->getParent()->getChildren()[0]);
        $this->assertEquals($children, $model->getChildren()[0]);
        $this->assertEquals($childrenChildren, $model->getChildren()[0]->getChildren()[0]);
    }
}
