<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Event;

use GibsonOS\Core\Exception\EventException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Mock\Service\TestEvent;
use GibsonOS\UnitTest\AbstractTest;

class AbstractEventTest extends AbstractTest
{
    private TestEvent $testEvent;

    private Event $event;

    private Element $element;

    protected function _before()
    {
        $this->testEvent = new TestEvent(
            $this->serviceManager->get(EventService::class),
            $this->serviceManager->get(ReflectionManager::class),
        );
        $this->event = new Event();
        $this->element = (new Element())
            ->setMethod('test')
            ->setParameters(['arthur' => 'dent'])
        ;
    }

    public function testRun(): void
    {
        $this->assertEquals('', $this->testEvent->arthur);
        $this->assertEquals('dent', $this->testEvent->run($this->element, $this->event));
        $this->assertEquals('dent', $this->testEvent->arthur);
    }

    public function testRunWrongMethod(): void
    {
        $this->element->setMethod('prefect');
        $this->expectException(EventException::class);
        $this->testEvent->run($this->element, $this->event);
    }

    public function testRunMethodWithoutAttribute(): void
    {
        $this->element->setMethod('noMethod');
        $this->expectException(EventException::class);
        $this->testEvent->run($this->element, $this->event);
    }

    public function testRunWrongParameter(): void
    {
        $this->element->setParameters(['marvin' => 'no hope']);
        $this->expectException(\Throwable::class);
        $this->testEvent->run($this->element, $this->event);
    }

    public function testRunWithException(): void
    {
        $this->element->setMethod('exception');
        $this->event->setExitOnError(false);
        $this->expectException(\Exception::class);
        $this->testEvent->run($this->element, $this->event);
    }

    public function testRunWithExitOnError(): void
    {
        $this->element->setMethod('exception');
        $this->assertNull($this->testEvent->run($this->element, $this->event));
    }
}
