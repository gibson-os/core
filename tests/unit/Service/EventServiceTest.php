<?php declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Codeception\Test\Unit;
use DateTime;
use GibsonOS\Core\Event\AbstractEvent;
use GibsonOS\Core\Event\Describer\DescriberInterface;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Repository\EventRepository;
use GibsonOS\Core\Service\Event\CodeGeneratorService;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class EventServiceTest extends Unit
{
    /**
     * @var EventService
     */
    private $eventService;

    /**
     * @var ServiceManagerService
     */
    private $serviceManagerService;

    /**
     * @var ObjectProphecy|EventRepository
     */
    private $eventServiceRepository;

    protected function _before(): void
    {
        putenv('TIMEZONE=Europe/Berlin');
        $this->serviceManagerService = new ServiceManagerService();
        $this->eventServiceRepository = $this->prophesize(EventRepository::class);
        $this->eventService = new EventService(
            $this->serviceManagerService,
            $this->eventServiceRepository->reveal(),
            $this->serviceManagerService->get(CodeGeneratorService::class)
        );
    }

    public function testFire(): void
    {
        $this->eventServiceRepository->getTimeControlled('arthur', Argument::type(DateTime::class))
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->eventServiceRepository->getTimeControlled('dent', Argument::type(DateTime::class))
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;

        $globalParams = null;

        $this->eventService->add('dent', function ($params) use (&$globalParams) {
            $globalParams = $params;
        });

        $this->eventService->fire('arthur', ['Handtuch' => true]);
        $this->assertNull($globalParams);
        $this->eventService->fire('dent', ['Handtuch' => true]);
        $this->assertEquals(['Handtuch' => true], $globalParams);
    }

    public function testRunFunction(): void
    {
        $element = (new Element())
            ->setClass(MarvinDescriber::class)
            ->setMethod('tears')
        ;

        $this->assertEquals('Will end in tears', $this->eventService->runFunction($element));
    }
}

class Marvin extends AbstractEvent
{
    public function __construct(MarvinDescriber $describer)
    {
        parent::__construct($describer);
    }

    public function tears(): string
    {
        return 'Will end in tears';
    }
}

class MarvinDescriber implements DescriberInterface
{
    public function getTitle(): string
    {
        return 'marvin';
    }

    public function getTriggers(): array
    {
        return [];
    }

    public function getMethods(): array
    {
        return [];
    }

    public function getEventClassName(): string
    {
        return Marvin::class;
    }
}
