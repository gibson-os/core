<?php namespace Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Service\AbstractSingletonService;
use GibsonOS\Core\Service\Registry;

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
        $instance = Registry::getInstance();
        $newInstance = Registry::getInstance();

        $this->assertTrue($instance instanceof AbstractSingletonService);
        $this->assertSame($instance, $newInstance);
    }
}