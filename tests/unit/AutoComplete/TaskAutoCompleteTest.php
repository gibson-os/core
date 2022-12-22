<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\AutoComplete;

use GibsonOS\Core\AutoComplete\TaskAutoComplete;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Repository\TaskRepository;
use Prophecy\Prophecy\ObjectProphecy;

class TaskAutoCompleteTest extends AbstractAutoCompleteTest
{
    private TaskRepository|ObjectProphecy $taskRepository;

    protected function _before()
    {
        $this->taskRepository = $this->prophesize(TaskRepository::class);
        $this->serviceManager->setService(TaskRepository::class, $this->taskRepository->reveal());

        parent::_before();
    }

    protected function getAutoCompleteClassName(): string
    {
        return TaskAutoComplete::class;
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
        $task = new Task();
        $this->taskRepository->getById(42)
            ->shouldBeCalledOnce()
            ->willReturn($task)
        ;

        $this->assertEquals($task, $this->autoComplete->getById('42', []));
    }
}
