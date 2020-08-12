<?php declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Model\Event\Element;
use GibsonOS\Core\Service\Event\AbstractEventService;
use GibsonOS\Core\Service\Event\Describer\DescriberInterface;

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

    protected function _before(): void
    {
        putenv('TIMEZONE=Europe/Berlin');
        $this->serviceManagerService = new ServiceManagerService();
        $this->eventService = $this->serviceManagerService->get(EventService::class);
    }

    public function testFire(): void
    {
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
            ->setClass(Marvin::class)
            ->setMethod('tears')
        ;

        $this->assertEquals('Will end in tears', $this->eventService->runFunction($element));
    }
}

class Marvin extends AbstractEventService
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
}
