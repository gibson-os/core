<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\AutoComplete;

use GibsonOS\Core\AutoComplete\EventAutoComplete;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Repository\EventRepository;
use Prophecy\Prophecy\ObjectProphecy;

class EventAutoCompleteTest extends UnitAutoCompleteTest
{
    private EventRepository|ObjectProphecy $eventRepository;

    protected function _before()
    {
        $this->eventRepository = $this->prophesize(EventRepository::class);

        parent::_before();
    }

    protected function getAutoComplete(): EventAutoComplete
    {
        return new EventAutoComplete($this->eventRepository->reveal());
    }

    public function testGetByNamePart(): void
    {
        $this->eventRepository->findByName('marvin', false)
            ->shouldBeCalledTimes(2)
            ->willReturn(['arthur'])
        ;
        $this->eventRepository->findByName('marvin', true)
            ->shouldBeCalledOnce()
            ->willReturn(['dent'])
        ;

        $this->assertEquals(['arthur'], $this->autoComplete->getByNamePart('marvin', []));
        $this->assertEquals(['dent'], $this->autoComplete->getByNamePart('marvin', ['onlyActive' => true]));
        $this->assertEquals(['arthur'], $this->autoComplete->getByNamePart('marvin', ['onlyActive' => false]));
    }

    public function testGetById(): void
    {
        $event = new Event($this->modelWrapper->reveal());
        $this->eventRepository->getById(42)
            ->shouldBeCalledOnce()
            ->willReturn($event)
        ;

        $this->assertEquals($event, $this->autoComplete->getById('42', []));
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
