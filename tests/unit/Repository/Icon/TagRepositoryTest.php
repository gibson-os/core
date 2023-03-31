<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\Icon;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\Icon\TagRepository;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;

class TagRepositoryTest extends Unit
{
    use ModelManagerTrait;

    private TagRepository $tagRepository;

    protected function _before()
    {
        $this->loadModelManager();

        $this->mysqlDatabase->getDatabaseName()
            ->shouldBeCalledOnce()
            ->willReturn('marvin')
        ;
        $this->mysqlDatabase->sendQuery('SHOW FIELDS FROM `marvin`.`icon_tag`')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->mysqlDatabase->fetchRow()
            ->shouldBeCalledTimes(3)
            ->willReturn(
                ['icon_id', 'bigint(42)', 'NO', '', null, ''],
                ['tag', 'varchar(42)', 'NO', '', null, ''],
                null
            )
        ;

        $this->tagRepository = new TagRepository('icon_tag');
    }

    public function testDeleteByIconId(): void
    {
        $this->mysqlDatabase->execute(
            'DELETE `icon_tag` FROM `marvin`.`icon_tag` WHERE `icon_id`=? ',
            [42],
        )
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->tagRepository->deleteByIconId(42);
    }
}
