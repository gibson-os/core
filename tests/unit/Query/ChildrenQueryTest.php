<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Query;

use Codeception\Test\Unit;
use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Query\ChildrenQuery;
use GibsonOS\Mock\Model\MockModel;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use MDO\Dto\Field;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Query\With;
use MDO\Dto\Select;
use MDO\Dto\Table;
use MDO\Enum\JoinType;
use MDO\Enum\Type;
use MDO\Query\SelectQuery;
use MDO\Service\SelectService;
use Prophecy\Prophecy\ObjectProphecy;

class ChildrenQueryTest extends Unit
{
    use ModelManagerTrait;

    private ReflectionManager $reflectionManager;

    private SelectService|ObjectProphecy $selectService;

    private ChildrenQuery $childrenQuery;

    protected function _before()
    {
        $this->loadModelManager();

        $this->selectService = $this->prophesize(SelectService::class);

        $this->childrenQuery = new ChildrenQuery(
            new ReflectionManager(),
            $this->tableManager->reveal(),
            $this->modelWrapper->reveal(),
            $this->selectService->reveal(),
        );
    }

    public function testGetQueryArray(): void
    {
        $table = new Table('marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;

        $this->assertEquals(
            (new SelectQuery($table, 'm'))
                ->setSelects([
                    'id' => '`m`.`id`',
                    'parent_id' => '`m`.`parent_id`',
                ])
                ->addWhere(new Where('`m`.`parent_id`=?', [42])),
            $this->childrenQuery->getSelectQuery(
                (new MockModel($this->modelWrapper->reveal()))->setId(42),
                'm',
                new ChildrenMapping('children', 'child_', 'c'),
            ),
        );
    }

    public function testGetQuery(): void
    {
        $table = new Table('marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledOnce()
            ->willReturn($table)
        ;

        $this->assertEquals(
            (new SelectQuery($table, 'm'))
                ->setSelects([
                    'id' => '`m`.`id`',
                    'parent_id' => '`m`.`parent_id`',
                ])
                ->addWhere(new Where('`m`.`id`=?', [42])),
            $this->childrenQuery->getSelectQuery(
                (new MockModel($this->modelWrapper->reveal()))->setParentId(42),
                'm',
                new ChildrenMapping('parent', 'parent_', 'p'),
            ),
        );
    }

    public function testGetQueryNested(): void
    {
        $table = new Table('marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(2)
            ->willReturn($table)
        ;
        $this->selectService->getSelects([new Select($table, 'pp', 'parent_parent_')])
            ->shouldBeCalledOnce()
            ->willReturn([
                'parent_parent_id' => '`pp`.`id`',
                'parent_parent_parent_id' => '`pp`.`parent_id`',
            ])
        ;

        $withTable = new Table('with_marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);
        $this->assertEquals(
            (new SelectQuery($withTable, 'm'))
                ->setSelects([
                    'id' => '`m`.`id`',
                    'parent_id' => '`m`.`parent_id`',
                    'parent_parent_id' => '`pp`.`id`',
                    'parent_parent_parent_id' => '`pp`.`parent_id`',
                ])
                ->setWith(new With('with_marvin', new SelectQuery($table, 'm')))
                ->addJoin(new Join($table, 'pp', '`m`.`parent_id`=`pp`.`id`', JoinType::LEFT))
                ->addWhere(new Where('`m`.`id`=?', [42])),
            $this->childrenQuery->getSelectQuery(
                (new MockModel($this->modelWrapper->reveal()))->setParentId(42),
                'm',
                new ChildrenMapping('parent', 'parent_', 'p', [
                    new ChildrenMapping('parent', 'parent_parent_', 'pp'),
                ]),
            ),
        );
    }

    public function testExtend(): void
    {
        $table = new Table('marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(2)
            ->willReturn($table)
        ;
        $this->selectService->getSelects([
            new Select($table, 'p', 'parent_'),
            new Select($table, 'c', 'child_'),
        ])
            ->shouldBeCalledOnce()
            ->willReturn([
                'parent_id' => '`p`.`id`',
                'parent_parent_id' => '`p`.`parent_id`',
                'child_id' => '`c`.`id`',
                'child_parent_id' => '`c`.`parent_id`',
            ])
        ;

        $withTable = new Table('with_marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);
        $this->assertEquals(
            (new SelectQuery($withTable, 'm'))
                ->setSelects([
                    'id' => '`m`.`id`',
                    'parent_id' => '`p`.`id`',
                    'parent_parent_id' => '`p`.`parent_id`',
                    'child_id' => '`c`.`id`',
                    'child_parent_id' => '`c`.`parent_id`',
                ])
                ->setWith(new With('with_marvin', new SelectQuery($table, 'm')))
                ->addJoin(new Join($table, 'p', '`m`.`parent_id`=`p`.`id`', JoinType::LEFT))
                ->addJoin(new Join($table, 'c', '`m`.`id`=`c`.`parent_id`', JoinType::LEFT)),
            $this->childrenQuery->extend(
                new SelectQuery($table, 'm'),
                MockModel::class,
                [
                    new ChildrenMapping('parent', 'parent_', 'p'),
                    new ChildrenMapping('children', 'child_', 'c'),
                ],
                'm',
            ),
        );
    }

    public function testExtendWithLimitAndOrder(): void
    {
        $table = new Table('marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(2)
            ->willReturn($table)
        ;
        $this->selectService->getSelects([
            new Select($table, 'p', 'parent_'),
            new Select($table, 'c', 'child_'),
        ])
            ->shouldBeCalledOnce()
            ->willReturn([
                'parent_id' => '`p`.`id`',
                'parent_parent_id' => '`p`.`parent_id`',
                'child_id' => '`c`.`id`',
                'child_parent_id' => '`c`.`parent_id`',
            ])
        ;

        $withTable = new Table('with_marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);
        $this->assertEquals(
            (new SelectQuery($withTable, 'm'))
                ->setSelects([
                    'id' => '`m`.`id`',
                    'parent_id' => '`p`.`id`',
                    'parent_parent_id' => '`p`.`parent_id`',
                    'child_id' => '`c`.`id`',
                    'child_parent_id' => '`c`.`parent_id`',
                ])
                ->setWith(new With(
                    'with_marvin',
                    (new SelectQuery($table, 'm'))
                        ->setLimit(10)
                        ->setOrder('`m`.`id`'),
                ))
                ->addJoin(new Join($table, 'p', '`m`.`parent_id`=`p`.`id`', JoinType::LEFT))
                ->addJoin(new Join($table, 'c', '`m`.`id`=`c`.`parent_id`', JoinType::LEFT))
                ->setOrder('`m`.`id`')
                ->setOrder('`p`.`id`'),
            $this->childrenQuery->extend(
                (new SelectQuery($table, 'm'))
                    ->setLimit(10)
                    ->setOrder('`m`.`id`')
                    ->setOrder('`p`.`id`'),
                MockModel::class,
                [
                    new ChildrenMapping('parent', 'parent_', 'p'),
                    new ChildrenMapping('children', 'child_', 'c'),
                ],
                'm',
            ),
        );
    }

    public function testExtendNested(): void
    {
        $table = new Table('marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);
        $this->tableManager->getTable('marvin')
            ->shouldBeCalledTimes(4)
            ->willReturn($table)
        ;
        $this->selectService->getSelects([
            new Select($table, 'p', 'parent_'),
            new Select($table, 'c', 'child_'),
        ])
            ->shouldBeCalledOnce()
            ->willReturn([
                'parent_id' => '`p`.`id`',
                'parent_parent_id' => '`p`.`parent_id`',
                'child_id' => '`c`.`id`',
                'child_parent_id' => '`c`.`parent_id`',
            ])
        ;
        $this->selectService->getSelects([new Select($table, 'pc', 'parent_child_')])
            ->shouldBeCalledOnce()
            ->willReturn([
                'parent_child_id' => '`pc`.`id`',
                'parent_child_parent_id' => '`pc`.`parent_id`',
            ])
        ;
        $this->selectService->getSelects([new Select($table, 'cc', 'child_child_')])
            ->shouldBeCalledOnce()
            ->willReturn([
                'child_child_id' => '`cc`.`id`',
                'child_child_parent_id' => '`cc`.`parent_id`',
            ])
        ;

        $withTable = new Table('with_marvin', [
            'id' => new Field('id', false, Type::BIGINT, '', null, '', 20),
            'parent_id' => new Field('parent_id', true, Type::BIGINT, '', null, '', 20),
        ]);
        $this->assertEquals(
            (new SelectQuery($withTable, 'm'))
                ->setSelects([
                    'id' => '`m`.`id`',
                    'parent_id' => '`p`.`id`',
                    'parent_parent_id' => '`p`.`parent_id`',
                    'child_id' => '`c`.`id`',
                    'child_parent_id' => '`c`.`parent_id`',
                    'parent_child_id' => '`pc`.`id`',
                    'parent_child_parent_id' => '`pc`.`parent_id`',
                    'child_child_id' => '`cc`.`id`',
                    'child_child_parent_id' => '`cc`.`parent_id`',
                ])
                ->setWith(new With('with_marvin', new SelectQuery($table, 'm')))
                ->addJoin(new Join($table, 'p', '`m`.`parent_id`=`p`.`id`', JoinType::LEFT))
                ->addJoin(new Join($table, 'pc', '`p`.`id`=`pc`.`parent_id`', JoinType::LEFT))
                ->addJoin(new Join($table, 'c', '`m`.`id`=`c`.`parent_id`', JoinType::LEFT))
                ->addJoin(new Join($table, 'cc', '`c`.`id`=`cc`.`parent_id`', JoinType::LEFT)),
            $this->childrenQuery->extend(
                new SelectQuery($table, 'm'),
                MockModel::class,
                [
                    new ChildrenMapping('parent', 'parent_', 'p', [
                        new ChildrenMapping('children', 'parent_child_', 'pc'),
                    ]),
                    new ChildrenMapping('children', 'child_', 'c', [
                        new ChildrenMapping('children', 'child_child_', 'cc'),
                    ]),
                ],
                'm',
            ),
        );
    }
}
