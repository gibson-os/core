<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\AutoComplete;

use GibsonOS\Core\AutoComplete\ActionAutoComplete;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Repository\ActionRepository;
use Prophecy\Prophecy\ObjectProphecy;

class ActionAutoCompleteTest extends UnitAutoCompleteTest
{
    private ActionRepository|ObjectProphecy $actionRepository;

    protected function _before(): void
    {
        $this->actionRepository = $this->prophesize(ActionRepository::class);

        parent::_before();
    }

    protected function getAutoComplete(): ActionAutoComplete
    {
        return new ActionAutoComplete($this->actionRepository->reveal());
    }

    public function testGetByNamePart(): void
    {
        $this->actionRepository->findByName('marvin', null)
            ->shouldBeCalledOnce()
            ->willReturn(['arthur'])
        ;
        $this->assertEquals(['arthur'], $this->autoComplete->getByNamePart('marvin', []));

        $this->actionRepository->findByName('marvin', 42)
            ->shouldBeCalledOnce()
            ->willReturn(['dent'])
        ;
        $this->assertEquals(['dent'], $this->autoComplete->getByNamePart('marvin', ['taskId' => 42]));
    }

    public function testGetById(): void
    {
        $action = new Action($this->mysqlDatabase->reveal());
        $this->actionRepository->getById(42)
            ->shouldBeCalledOnce()
            ->willReturn($action)
        ;

        $this->assertEquals($action, $this->autoComplete->getById('42', []));
    }

    protected function getValueField(): string
    {
        return 'id';
    }

    protected function getDisplayField(): string
    {
        return 'name';
    }
}
