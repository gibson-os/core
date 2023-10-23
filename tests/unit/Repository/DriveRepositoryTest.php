<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use DateTimeImmutable;
use GibsonOS\Core\Model\Drive;
use GibsonOS\Core\Repository\DriveRepository;
use MDO\Dto\Field;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Dto\Table;
use MDO\Enum\Type;
use MDO\Query\SelectQuery;

class DriveRepositoryTest extends Unit
{
    use RepositoryTrait;

    private DriveRepository $driveRepository;

    private Table $driveStatTable;

    protected function _before()
    {
        $this->loadRepository('drive');

        $this->driveStatTable = new Table(
            'drive_stat',
            [
                new Field('drive_id', false, Type::BIGINT, '', null, '', 42),
                new Field('disk', false, Type::BIGINT, '', null, '', 42),
            ],
        );
        $driveStatTable = $this->driveStatTable;

        $this->driveRepository = new DriveRepository(
            $this->repositoryWrapper->reveal(),
            $this->table->getTableName(),
            'drive_stat',
        );
    }

    public function testGetDrivesWithAttributes(): void
    {
        $selectQuery = (new SelectQuery($this->table, 'd'))
            ->addJoin(new Join($this->driveStatTable, 'ds', '`d`.`id`=`ds`.`drive_id`'))
            ->addWhere(new Where(
                'UNIX_TIMESTAMP(`ds`.`added`)>=UNIX_TIMESTAMP(NOW())-?',
                [900],
            ))
        ;

        $model = $this->loadModel($selectQuery, Drive::class, '');
        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledOnce()
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledTimes(2)
        ;
        $this->tableManager->getTable($this->driveStatTable->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->driveStatTable)
        ;
        $drive = $this->driveRepository->getDrivesWithAttributes()[0];

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $drive->setAdded($date);

        $this->assertEquals($model, $drive);
    }

    public function testGetDrivesWithAttributesChangedSeconds(): void
    {
        $selectQuery = (new SelectQuery($this->table, 'd'))
            ->addJoin(new Join($this->driveStatTable, 'ds', '`d`.`id`=`ds`.`drive_id`'))
            ->addWhere(new Where(
                'UNIX_TIMESTAMP(`ds`.`added`)>=UNIX_TIMESTAMP(NOW())-?',
                [42],
            ))
        ;

        $model = $this->loadModel($selectQuery, Drive::class, '');
        $this->repositoryWrapper->getModelWrapper()
            ->shouldBeCalledOnce()
        ;
        $this->repositoryWrapper->getTableManager()
            ->shouldBeCalledTimes(2)
        ;
        $this->tableManager->getTable($this->driveStatTable->getTableName())
            ->shouldBeCalledOnce()
            ->willReturn($this->driveStatTable)
        ;
        $drive = $this->driveRepository->getDrivesWithAttributes(42)[0];

        $date = new DateTimeImmutable();
        $model->setAdded($date);
        $drive->setAdded($date);

        $this->assertEquals($model, $drive);
    }
}
