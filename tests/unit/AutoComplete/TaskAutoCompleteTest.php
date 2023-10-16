<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\AutoComplete;

use GibsonOS\Core\AutoComplete\TaskAutoComplete;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Repository\TaskRepository;
use Prophecy\Prophecy\ObjectProphecy;

class TaskAutoCompleteTest extends UnitAutoCompleteTest
{
    private TaskRepository|ObjectProphecy $taskRepository;

    protected function _before()
    {
        $this->taskRepository = $this->prophesize(TaskRepository::class);

        parent::_before();
    }

    protected function getAutoComplete(): TaskAutoComplete
    {
        return new TaskAutoComplete($this->taskRepository->reveal());
    }

    public function testGetByNamePart(): void
    {
        $this->taskRepository->findByName('marvin', null)
            ->shouldBeCalledOnce()
            ->willReturn(['arthur'])
        ;
        $this->assertEquals(['arthur'], $this->autoComplete->getByNamePart('marvin', []));

        $this->taskRepository->findByName('marvin', 42)
            ->shouldBeCalledOnce()
            ->willReturn(['dent'])
        ;
        $this->assertEquals(['dent'], $this->autoComplete->getByNamePart('marvin', ['moduleId' => 42]));
    }

    public function testGetById(): void
    {
        $task = new Task($this->modelWrapper->reveal());
        $this->taskRepository->getById(42)
            ->shouldBeCalledOnce()
            ->willReturn($task)
        ;

        $this->assertEquals($task, $this->autoComplete->getById('42', []));
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
