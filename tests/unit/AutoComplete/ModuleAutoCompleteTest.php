<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\AutoComplete;

use GibsonOS\Core\AutoComplete\ModuleAutoComplete;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Repository\ModuleRepository;
use Prophecy\Prophecy\ObjectProphecy;

class ModuleAutoCompleteTest extends UnitAutoCompleteTest
{
    private ModuleRepository|ObjectProphecy $moduleRepository;

    protected function _before()
    {
        $this->moduleRepository = $this->prophesize(ModuleRepository::class);
        $this->serviceManager->setService(ModuleRepository::class, $this->moduleRepository->reveal());

        parent::_before();
    }

    protected function getAutoCompleteClassName(): string
    {
        return ModuleAutoComplete::class;
    }

    public function testGetByNamePart(): void
    {
        $this->moduleRepository->findByName('marvin')
            ->shouldBeCalledOnce()
            ->willReturn(['arthur'])
        ;

        $this->assertEquals(['arthur'], $this->autoComplete->getByNamePart('marvin', []));
    }

    public function testGetById(): void
    {
        $module = new Module();
        $this->moduleRepository->getById(42)
            ->shouldBeCalledOnce()
            ->willReturn($module)
        ;

        $this->assertEquals($module, $this->autoComplete->getById('42', []));
    }
}
