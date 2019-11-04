<?php declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\SetError;
use phpmock\phpunit\PHPMock;
use Throwable;
use UnitTester;

class EnvTest extends Unit
{
    use PHPMock;

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var EnvService
     */
    private $env;

    protected function _before()
    {
        $this->env = new EnvService();
    }

    protected function _after()
    {
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
        } else {
            $this->mockPutEnv($value);
        }

        $this->env->setInt('answer', $value);
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testGetInt($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        $this->mockGetEnv($value);

        if ($isInt) {
            $this->assertEquals($value, $this->env->getInt('answer'));
        } else {
            $this->assertNotSame($value, $this->env->getInt('answer'));
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
        } else {
            $this->mockPutEnv($value);
        }

        $this->env->setFloat('answer', $value);
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testGetFloat($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        $this->mockGetEnv($value);

        if ($isFloat) {
            $this->assertEquals($value, $this->env->getFloat('answer'));
        } else {
            $this->assertNotSame($value, $this->env->getFloat('answer'));
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
        } else {
            $this->mockPutEnv($value);
        }

        $this->env->setString('answer', $value);
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testGetString($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        $this->mockGetEnv($value);

        if ($isString) {
            $this->assertEquals($value, $this->env->getString('answer'));
        } else {
            $this->assertNotSame($value, $this->env->getString('answer'));
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
        } else {
            $this->mockPutEnv($value);
        }

        $this->env->setBool('answer', $value);
    }

    /**
     * @dataProvider getData
     *
     * @param mixed $value
     */
    public function testGetBool($value, bool $isInt, bool $isFloat, bool $isString, bool $isBool): void
    {
        $this->mockGetEnv($value);

        if ($isBool) {
            $this->assertEquals($value, $this->env->getBool('answer'));
        } else {
            $this->assertNotSame($value, $this->env->getBool('answer'));
        }
    }

    public function testSetEmpty(): void
    {
        $this->getFunctionMock(__NAMESPACE__, 'putenv')
            ->expects($this->once())
            ->with('ANSWER')
            ->willReturn(true)
        ;

        $this->env->setEmpty('answer');
    }

    public function testSetError(): void
    {
        $this->expectException(SetError::class);
        $this->getFunctionMock(__NAMESPACE__, 'putenv')
            ->expects($this->once())
            ->with('ANSWER=42')
            ->willReturn(false)
        ;

        $this->env->setInt('answer', 42);
    }

    public function testGetError(): void
    {
        $this->expectException(GetError::class);
        $this->getFunctionMock(__NAMESPACE__, 'getenv')
            ->expects($this->once())
            ->with('ANSWER')
            ->willReturn(false)
        ;

        $this->env->getInt('answer');
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

    private function mockGetEnv($value): void
    {
        $this->getFunctionMock(__NAMESPACE__, 'getenv')
            ->expects($this->once())
            ->with('ANSWER')
            ->willReturn(
                is_bool($value)
                ? $value === true ? 'true' : 'false'
                : (string) $value
            )
        ;
    }

    private function mockPutEnv($value): void
    {
        $this->getFunctionMock(__NAMESPACE__, 'putenv')
            ->expects($this->once())
            ->with('ANSWER=' . (is_bool($value) ? $value === true ? 'true' : 'false' : $value))
            ->willReturn(true)
        ;
    }
}
