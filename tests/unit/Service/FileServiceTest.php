<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Core\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class FileServiceTest extends Unit
{
    use ProphecyTrait;

    private FileService $fileService;

    private DirService|ObjectProphecy $dirService;

    protected function _before()
    {
        $this->dirService = $this->prophesize(DirService::class);

        $this->fileService = new FileService($this->dirService->reveal());
    }

    public function testExists(): void
    {
        $this->assertTrue($this->fileService->exists(__FILE__));
    }

    public function testNotExists(): void
    {
        $this->assertFalse($this->fileService->exists('marvin'));
    }

    public function testIsReadable(): void
    {
        $this->assertTrue($this->fileService->isReadable(__FILE__));
    }

    public function testIsNotReadable(): void
    {
        $this->assertFalse($this->fileService->isReadable('marvin'));
    }

    /**
     * @dataProvider getFileEndingData
     */
    public function testGetFileEnding(string $path, string $ending): void
    {
        $this->assertEquals($ending, $this->fileService->getFileEnding($path));
    }

    public static function getFileEndingData(): array
    {
        $ds = DIRECTORY_SEPARATOR;

        return [
            ['arthur.dent', 'dent'],
            [sprintf('%sarthur.dent', $ds), 'dent'],
            [sprintf('arthur%sdent', $ds), 'dent'],
            [sprintf('%sarthur%sdent', $ds, $ds), 'dent'],
            [sprintf('arthur%s.dent', $ds), 'dent'],
            [sprintf('%sarthur%s.dent', $ds, $ds), 'dent'],
            [sprintf('ford.prefect%sdent', $ds), 'dent'],
            [sprintf('%sford.prefect%sdent', $ds, $ds), 'dent'],
            [sprintf('ford.prefect%sarthur.dent', $ds), 'dent'],
            [sprintf('%sford.prefect%sarthur.dent', $ds, $ds), 'dent'],
        ];
    }
}
