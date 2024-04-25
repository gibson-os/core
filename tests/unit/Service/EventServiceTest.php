<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use DateTime;
use GibsonOS\Core\Exception\Lock\LockException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\Event;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\Event\ElementService;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Core\Service\LoggerService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Mock\Service\TestEvent;
use GibsonOS\Test\Unit\Core\ModelManagerTrait;
use MDO\Client;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class EventServiceTest extends Unit
{
    use ModelManagerTrait;

    private EventService $eventService;

    private EventRepository|ObjectProphecy $eventRepository;

    private LockService|ObjectProphecy $lockService;

    private ServiceManager $serviceManager;

    protected function _before(): void
    {
        $this->loadModelManager();

        $this->serviceManager = new ServiceManager();
        $this->serviceManager->setInterface(LoggerInterface::class, LoggerService::class);
        $this->serviceManager->setService(Client::class, $this->client->reveal());
        $this->serviceManager->setService(ModelManager::class, $this->modelManager->reveal());

        $this->eventRepository = $this->prophesize(EventRepository::class);
        $this->lockService = $this->prophesize(LockService::class);

        $this->eventService = new EventService(
            $this->serviceManager,
            $this->eventRepository->reveal(),
            $this->serviceManager->get(ElementService::class),
            $this->serviceManager->get(CommandService::class),
            $this->serviceManager->get(DateTimeService::class),
            $this->serviceManager->get(ReflectionManager::class),
            $this->serviceManager->get(ModelManager::class),
            $this->serviceManager->get(ProcessService::class),
            $this->serviceManager->get(LoggerInterface::class),
            $this->lockService->reveal(),
        );
    }

    public function testFire(): void
    {
        $this->eventRepository->getTimeControlled(
            TestEvent::class,
            TestEvent::TRIGGER_MARVIN,
            Argument::type(DateTime::class),
        )
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->eventRepository->getTimeControlled(
            TestEvent::class,
            TestEvent::TRIGGER_FORD,
            Argument::type(DateTime::class),
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

    #[DataProvider('getTestData')]
    public function testRunEvent(Event $event, string $returnValue): void
    {
        $testEvent = $this->serviceManager->get(TestEvent::class);
        $this->modelManager->saveWithoutChildren(Argument::any())
            ->shouldBeCalledTimes(2)
        ;
        $this->lockService->lock('event0')
            ->shouldNotBeCalled()
        ;
        $this->lockService->unlock('event0')
            ->shouldNotBeCalled()
        ;

        $this->eventService->runEvent($event, false);

        $this->assertEquals($returnValue, $testEvent->arthur);
    }

    #[DataProvider('getTestData')]
    public function testRunEventLocked(Event $event): void
    {
        $event->setLockCommand(true);
        $testEvent = $this->serviceManager->get(TestEvent::class);
        $this->lockService->lock('event0')
            ->shouldBeCalledOnce()
            ->willThrow(LockException::class)
        ;
        $this->lockService->unlock('event0')
            ->shouldNotBeCalled()
        ;

        $this->eventService->runEvent($event, false);

        $this->assertEquals('', $testEvent->arthur);
    }

    #[DataProvider('getTestData')]
    public function testRunEventNotLocked(Event $event, string $returnValue): void
    {
        $event->setLockCommand(true);
        $testEvent = $this->serviceManager->get(TestEvent::class);
        $this->lockService->lock('event0')
            ->shouldBeCalledOnce()
        ;
        $this->lockService->unlock('event0')
            ->shouldBeCalledOnce()
        ;

        $this->eventService->runEvent($event, false);

        $this->assertEquals($returnValue, $testEvent->arthur);
    }

    public function getTestData(): array
    {
        $modelWrapper = $this->prophesize(ModelWrapper::class);

        return [
            'Simple Event' => [
                (new Event($modelWrapper->reveal()))
                    ->setAsync(false)
                    ->setElements([
                        (new Element($modelWrapper->reveal()))
                            ->setClass(TestEvent::class)
                            ->setMethod('test')
                            ->setParameters(['arthur' => 'dent']),
                    ])
                    ->setTriggers([
                        (new Event\Trigger($modelWrapper->reveal()))
                            ->setClass(TestEvent::class)
                            ->setTrigger(TestEvent::TRIGGER_FORD),
                    ]),
                'dent',
            ],
        ];
    }
}
