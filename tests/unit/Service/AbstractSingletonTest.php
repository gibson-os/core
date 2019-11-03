<?php
declare(strict_types=1);

namespace Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Service\AbstractSingletonService;
use GibsonOS\Core\Service\FlockService;

class AbstractSingletonTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testNewInstance(): void
    {
        $instance = FlockService::getInstance();
        $newInstance = FlockService::getInstance();

        $this->assertTrue($instance instanceof AbstractSingletonService);
        $this->assertSame($instance, $newInstance);
    }
}
