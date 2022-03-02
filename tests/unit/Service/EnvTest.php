<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\EnvService;
use Throwable;
use UnitTester;

class EnvTest extends Unit
{
    protected UnitTester $tester;

    private EnvService $envService;

    protected function _before()
    {
        $this->envService = new EnvService();
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testSetInt($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        if (!$isInt) {
            $this->expectException(Throwable::class);
        }

        $this->envService->setInt('answer', $value);
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testGetInt($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        putenv('ANSWER=' . $value);

        if ($isInt) {
            $this->assertEquals($value, $this->envService->getInt('answer'));
        } else {
            $this->assertNotSame($value, $this->envService->getInt('answer'));
        }
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testSetFloat($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        if (!$isFloat) {
            $this->expectException(Throwable::class);
        }

        $this->envService->setFloat('answer', $value);
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testGetFloat($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        putenv('ANSWER=' . $value);

        if ($isFloat) {
            $this->assertEquals($value, $this->envService->getFloat('answer'));
        } else {
            $this->assertNotSame($value, $this->envService->getFloat('answer'));
        }
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testSetString($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        if (!$isString) {
            $this->expectException(Throwable::class);
        }

        $this->envService->setString('answer', $value);
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testGetString($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        putenv('ANSWER=' . $value);

        if ($isString) {
            $this->assertEquals($value, $this->envService->getString('answer'));
        } else {
            $this->assertNotSame($value, $this->envService->getString('answer'));
        }
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testSetBool($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        if (!$isBool) {
            $this->expectException(Throwable::class);
        }

        $this->envService->setBool('answer', $value);
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testGetBool($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        putenv('ANSWER=' . ($value ? 'true' : 'false'));

        if ($isBool) {
            $this->assertEquals($value, $this->envService->getBool('answer'));
        } else {
            $this->assertNotSame($value, $this->envService->getBool('answer'));
        }
    }

    public function testGetError(): void
    {
        $this->expectException(GetError::class);
        $this->envService->getInt('galaxy');
    }

    public function getData(): array
    {
        // value, int, float, string, bool
        return [
            [42, true, true, false, false],
            [42.42, false, true, false, false],
            ['the answer', false, false, true, false],
            [true, false, false, false, true],
            [false, false, false, false, true],
        ];
    }
}
