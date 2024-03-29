<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\RegistryService;
use stdClass;
use UnitTester;

class RegistryTest extends Unit
{
    protected UnitTester $tester;

    private RegistryService $registry;

    protected function _before(): void
    {
        $this->registry = new RegistryService();
        $this->registry->set('arthur', 'dent');
    }

    protected function _after()
    {
    }

    /**
     * @throws GetError
     */
    public function testSetObject(): void
    {
        $object = new stdClass();
        $object->marvin = true;
        $object->herz = 'aus Gold';
        $this->registry->set('object', $object);

        $this->assertSame($object, $this->registry->get('object'));
    }

    /**
     * @throws GetError
     */
    public function testSetArray(): void
    {
        $array = [
            'antwort' => 'auf alles',
            42 => true,
        ];
        $this->registry->set('array', $array);

        $this->assertSame($array, $this->registry->get('array'));
    }

    /**
     * @throws GetError
     */
    public function testSetInt(): void
    {
        $this->registry->set('answer', 42);

        $this->assertSame(42, $this->registry->get('answer'));
    }

    /**
     * @throws GetError
     */
    public function testSetFloat(): void
    {
        $this->registry->set('answer', 42.00042);
        $this->assertSame(42.00042, $this->registry->get('answer'));
    }

    /**
     * @throws GetError
     */
    public function testSetBool(): void
    {
        $this->registry->set('answer', true);

        $this->assertSame(true, $this->registry->get('answer'));
    }

    public function testExists(): void
    {
        $this->assertTrue($this->registry->exists('arthur'));
    }

    public function testNotExists(): void
    {
        $this->assertFalse($this->registry->exists('marvin'));
    }

    /**
     * @throws GetError
     */
    public function testGetException(): void
    {
        $this->expectException(GetError::class);

        $this->registry->get('marvin');
    }
}
