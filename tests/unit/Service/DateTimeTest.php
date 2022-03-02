<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Service;

use Codeception\Test\Unit;
use DateTime;
use DateTimeZone;
use GibsonOS\Core\Service\DateTimeService;
use UnitTester;

class DateTimeTest extends Unit
{
    protected UnitTester $tester;

    private DateTimeService $dateTime;

    private DateTimeZone $timeZone;

    protected function _before()
    {
        $this->timeZone = new DateTimeZone('Europe/Berlin');
        $this->dateTime = new DateTimeService('Europe/Berlin', 51.2642156, 6.8001438);
    }

    protected function _after()
    {
    }

    public function testGet()
    {
        $testDateTime = new DateTime('28.04.2005 00:00:00', $this->timeZone);
        $dateTime = $this->dateTime->get('2005-04-28 00:00:00');

        $this->assertEquals($testDateTime->getTimestamp(), $dateTime->getTimestamp());
    }

    public function testGetInvalidDate()
    {
        $this->dateTime->get('not today');
    }

    public function testSetTimezone()
    {
        $this->dateTime->setTimezone($this->timeZone);
    }
}
