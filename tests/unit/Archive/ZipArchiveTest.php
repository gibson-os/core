<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Archive;

use GibsonOS\Core\Archive\ZipArchive;
use GibsonOS\Core\Exception\ArchiveException;
use GibsonOS\Test\Unit\Core\UnitTest;
use Prophecy\Prophecy\ObjectProphecy;
use ZipArchive as StandardZipArchive;

class ZipArchiveTest extends UnitTest
{
    private StandardZipArchive|ObjectProphecy $zipArchive;

    protected function _before(): void
    {
        $this->zipArchive = $this->prophesize(StandardZipArchive::class);
        $this->serviceManager->setService(StandardZipArchive::class, $this->zipArchive->reveal());
    }

    public function testPackFiles(): void
    {
        $this->zipArchive->open('marvin.zip', StandardZipArchive::CREATE)
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->zipArchive->addFile('trillian.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->zipArchive->addFile('arthur.txt')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->zipArchive->addFile('dent.gif')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->zipArchive->close()
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $zipArchive = $this->serviceManager->get(ZipArchive::class);
        $zipArchive->packFiles('marvin.zip', [
            'trillian.pdf',
            'arthur.txt',
            'dent.gif',
        ]);
    }

    public function testPackFilesOpenError(): void
    {
        $this->zipArchive->open('marvin.zip', StandardZipArchive::CREATE)
            ->shouldBeCalledOnce()
            ->willReturn(StandardZipArchive::ER_OPEN)
        ;

        $this->expectException(ArchiveException::class);
        $this->serviceManager->get(ZipArchive::class)->packFiles('marvin.zip', ['arthur.txt']);
    }

    public function testPackFilesAddError(): void
    {
        $this->zipArchive->open('marvin.zip', StandardZipArchive::CREATE)
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->zipArchive->addFile('arthur.txt')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->expectException(ArchiveException::class);
        $this->serviceManager->get(ZipArchive::class)->packFiles('marvin.zip', ['arthur.txt']);
    }

    public function testPackFilesCloseError(): void
    {
        $this->zipArchive->open('marvin.zip', StandardZipArchive::CREATE)
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->zipArchive->addFile('arthur.txt')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->zipArchive->close()
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->expectException(ArchiveException::class);
        $this->serviceManager->get(ZipArchive::class)->packFiles('marvin.zip', ['arthur.txt']);
    }
}
