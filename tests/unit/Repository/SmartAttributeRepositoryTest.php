<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Repository;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\SmartAttribute;
use GibsonOS\Core\Repository\SmartAttributeRepository;
use MDO\Dto\Query\Where;
use MDO\Query\SelectQuery;

class SmartAttributeRepositoryTest extends Unit
{
    use RepositoryTrait;

    private SmartAttributeRepository $smartAttributeRepository;

    protected function _before()
    {
        $this->loadRepository('system_smart_attribute');

        $this->smartAttributeRepository = new SmartAttributeRepository($this->repositoryWrapper->reveal());
    }

    public function testGetAll(): void
    {
        $selectQuery = (new SelectQuery($this->table))
            ->addWhere(new Where('1', []))
        ;

        $this->assertEquals(
            $this->loadModel($selectQuery, SmartAttribute::class, ''),
            $this->smartAttributeRepository->getAll()[0],
        );
    }
}
