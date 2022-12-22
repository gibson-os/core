<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\AutoComplete;

use GibsonOS\Core\AutoComplete\ActionAutoComplete;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Repository\ActionRepository;
use Prophecy\Prophecy\ObjectProphecy;

class ActionAutoCompleteTest extends AbstractAutoCompleteTest
{
    private ActionRepository|ObjectProphecy $actionRepository;

    protected function _before()
    {
        $this->actionRepository = $this->prophesize(ActionRepository::class);
        $this->serviceManager->setService(ActionRepository::class, $this->actionRepository->reveal());

        parent::_before();
    }

    protected function getAutoCompleteClassName(): string
    {
        return ActionAutoComplete::class;
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
        $action = new Action();
        $this->actionRepository->getById(42)
            ->shouldBeCalledOnce()
            ->willReturn($action)
        ;

        $this->assertEquals($action, $this->autoComplete->getById('42', []));
    }
}
