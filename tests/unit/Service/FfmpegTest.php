<?php declare(strict_types=1);

namespace Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Service\DateTime;
use GibsonOS\Core\Service\Ffmpeg;
use GibsonOS\Core\Service\File;
use Prophecy\Prophecy\ObjectProphecy;
use UnitTester;

class FfmpegTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var string
     */
    private $ffmpegPath;

    /**
     * @var Ffmpeg
     */
    private $ffmpeg;

    /**
     * @var ObjectProphecy|DateTime
     */
    private $dateTime;

    /**
     * @var ObjectProphecy|File
     */
    private $file;

    protected function _before()
    {
        $this->ffmpegPath = 'path/to/ffmpeg';
        $this->dateTime = $this->prophesize(DateTime::class);
        $this->file = $this->prophesize(File::class);

        $this->ffmpeg = new Ffmpeg($this->ffmpegPath, $this->dateTime->reveal(), $this->file->reveal());
    }

    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {
    }
}
