<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Archive;

use Codeception\Test\Unit;
use GibsonOS\Core\Archive\ZipArchive;
use GibsonOS\Core\Exception\ArchiveException;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ZipArchive as StandardZipArchive;

class ZipArchiveTest extends Unit
{
    use ProphecyTrait;

    private ZipArchive $zipArchive;

    private StandardZipArchive|ObjectProphecy $standardZipArchive;

    protected function _before(): void
    {
        $this->standardZipArchive = $this->prophesize(StandardZipArchive::class);

        $this->zipArchive = new ZipArchive($this->standardZipArchive->reveal());
    }

    public function testPackFiles(): void
    {
        $this->standardZipArchive->open('marvin.zip', StandardZipArchive::CREATE)
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->standardZipArchive->addFile('trillian.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->standardZipArchive->addFile('arthur.txt')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->standardZipArchive->addFile('dent.gif')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->standardZipArchive->close()
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->zipArchive->packFiles('marvin.zip', [
            'trillian.pdf',
            'arthur.txt',
            'dent.gif',
        ]);
    }

    public function testPackFilesOpenError(): void
    {
        $this->standardZipArchive->open('marvin.zip', StandardZipArchive::CREATE)
            ->shouldBeCalledOnce()
            ->willReturn(StandardZipArchive::ER_OPEN)
        ;

        $this->expectException(ArchiveException::class);
        $this->zipArchive->packFiles('marvin.zip', ['arthur.txt']);
    }

    public function testPackFilesAddError(): void
    {
        $this->standardZipArchive->open('marvin.zip', StandardZipArchive::CREATE)
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->standardZipArchive->addFile('arthur.txt')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->expectException(ArchiveException::class);
        $this->zipArchive->packFiles('marvin.zip', ['arthur.txt']);
    }

    public function testPackFilesCloseError(): void
    {
        $this->standardZipArchive->open('marvin.zip', StandardZipArchive::CREATE)
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->standardZipArchive->addFile('arthur.txt')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->standardZipArchive->close()
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->expectException(ArchiveException::class);
        $this->zipArchive->packFiles('marvin.zip', ['arthur.txt']);
    }
}
