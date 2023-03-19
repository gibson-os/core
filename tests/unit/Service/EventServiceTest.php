<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service;

use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\Event\ElementService;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Mock\Service\TestEvent;
use GibsonOS\Test\Unit\Core\UnitTest;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class EventServiceTest extends UnitTest
{
    use ProphecyTrait;

    private EventService $eventService;

    /**
     * @var ObjectProphecy|EventRepository
     */
    private $eventRepository;

    protected function _before(): void
    {
        $this->eventRepository = $this->prophesize(EventRepository::class);
        $this->eventService = new EventService(
            $this->serviceManager,
            $this->eventRepository->reveal(),
            $this->serviceManager->get(ElementService::class),
            $this->serviceManager->get(CommandService::class),
            $this->serviceManager->get(DateTimeService::class),
            $this->serviceManager->get(ReflectionManager::class),
            $this->serviceManager->get(ModelManager::class),
            $this->serviceManager->get(ProcessService::class),
            $this->serviceManager->get(LoggerInterface::class)
        );
    }

    public function testFire(): void
    {
        $this->eventRepository->getTimeControlled(
            TestEvent::class,
            TestEvent::TRIGGER_MARVIN,
            Argument::type(\DateTime::class)
        )
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->eventRepository->getTimeControlled(
            TestEvent::class,
            TestEvent::TRIGGER_FORD,
            Argument::type(\DateTime::class)
        )
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;

        $globalParams = null;

        $this->eventService->add(TestEvent::class, TestEvent::TRIGGER_FORD, function ($params) use (&$globalParams) {
            $globalParams = $params;
        });

        $this->eventService->fire(TestEvent::class, TestEvent::TRIGGER_MARVIN, ['Handtuch' => true]);
        $this->assertNull($globalParams);
        $this->eventService->fire(TestEvent::class, TestEvent::TRIGGER_FORD, ['Handtuch' => true]);
        $this->assertEquals(['Handtuch' => true], $globalParams);
    }

    /**
     * @dataProvider getTestData
     */
    public function testFireTimeControlled(Event $event, string $returnValue): void
    {
        $this->eventRepository->getTimeControlled(TestEvent::class, TestEvent::TRIGGER_MARVIN, Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->eventRepository->getTimeControlled(TestEvent::class, TestEvent::TRIGGER_FORD, Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn([$event])
        ;
        $this->modelManager->saveWithoutChildren(Argument::any())
            ->shouldBeCalledTimes(2)
        ;
        $this->eventService->fire(TestEvent::class, TestEvent::TRIGGER_MARVIN);
        $this->assertEquals('', $this->serviceManager->get(TestEvent::class)->arthur);
        $this->eventService->fire(TestEvent::class, TestEvent::TRIGGER_FORD);
        $this->assertEquals($returnValue, $this->serviceManager->get(TestEvent::class)->arthur);
    }

    /**
     * @dataProvider getTestData
     */
    public function testRunEvent(Event $event, string $returnValue): void
    {
        $testEvent = $this->serviceManager->get(TestEvent::class);
        $this->eventService->runEvent($event, false);
        $this->modelManager->saveWithoutChildren(Argument::any())->shouldBeCalledTimes(2);

        $this->assertEquals($returnValue, $testEvent->arthur);
    }

    public function getTestData(): array
    {
        return [
            'Simple Event' => [
                (new Event())
                    ->setAsync(false)
                    ->setElements([
                        (new Element())
                            ->setClass(TestEvent::class)
                            ->setMethod('test')
                            ->setParameters(['arthur' => 'dent']),
                    ])
                    ->setTriggers([
                        (new Event\Trigger())
                            ->setClass(TestEvent::class)
                            ->setTrigger(TestEvent::TRIGGER_FORD),
                    ]),
                'dent',
            ],
        ];
    }
}
