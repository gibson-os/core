<?php declare(strict_types=1);

namespace Service;

use Codeception\Test\Unit;
use DateTime as PhpDateTime;
use DateTimeZone;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Service\DateTime;
use UnitTester;

class DateTimeTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var DateTimeZone
     */
    private $timeZone;

    protected function _before()
    {
        $this->timeZone = new DateTimeZone('Europe/Berlin');
        $this->dateTime = new DateTime($this->timeZone);
    }

    protected function _after()
    {
    }

    public function testNew()
    {
        $testDateTime = new PhpDateTime('now', $this->timeZone);
        $dateTime = $this->dateTime->new();

        $this->assertGreaterThanOrEqual($testDateTime->getTimestamp(), $dateTime->getTimestamp());
    }

    public function testGet()
    {
        $testDateTime = new PhpDateTime('28.04.2005 00:00:00', $this->timeZone);
        $dateTime = $this->dateTime->get('2005-04-28 00:00:00');

        $this->assertEquals($testDateTime->getTimestamp(), $dateTime->getTimestamp());
    }

    public function testGetError()
    {
        $this->expectException(DateTimeError::class);

        $this->dateTime->get('not today');
    }

    public function testSetTimezone()
    {
        $this->dateTime->setTimezone($this->timeZone);
    }
}
