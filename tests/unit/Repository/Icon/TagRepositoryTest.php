<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository\Icon;

use Codeception\Test\Unit;
use GibsonOS\Core\Repository\Icon\TagRepository;
use GibsonOS\Test\Unit\Core\Repository\RepositoryTrait;
use MDO\Dto\Query\Where;
use MDO\Query\DeleteQuery;

class TagRepositoryTest extends Unit
{
    use RepositoryTrait;

    private TagRepository $tagRepository;

    protected function _before()
    {
        $this->loadRepository('icon_tag');

        $this->tagRepository = new TagRepository($this->repositoryWrapper->reveal(), 'icon_tag');
    }

    public function testDeleteByIconId(): void
    {
        $deleteQuery = (new DeleteQuery($this->table))
            ->addWhere(new Where('`icon_id`=?', [42]))
        ;
        $this->loadDeleteQuery($deleteQuery);

        $this->assertTrue($this->tagRepository->deleteByIconId(42));
    }
}
