<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DirTest extends Unit
{
    use ProphecyTrait;

    protected \UnitTester $tester;

    private DirService $dir;

    private string $dirName;

    protected function _before(): void
    {
        $this->dirName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'newDir';
        $this->dir = new DirService();

        if (file_exists($this->dirName)) {
            rmdir($this->dirName);
        }
    }

    protected function _after()
    {
    }

    public function testCreate()
    {
        $this->dir->create($this->dirName);

        $this->assertDirectoryExists($this->dirName);
    }

    public function testCreateExistingDirectory(): void
    {
        $this->expectException(CreateError::class);

        $this->dir->create($this->dirName);
        $this->dir->create($this->dirName);
    }

    public function testCreateRecursiveDirectory(): void
    {
        $this->dirName .= DIRECTORY_SEPARATOR . 'inNewDir';
        $this->dir->create($this->dirName);

        $this->assertDirectoryExists($this->dirName);
        rmdir($this->dirName);
    }

    /**
     * @dataProvider getAddEndSlashData
     */
    public function testAddEndSlash(string $inputDirName, ?string $slash, string $outputDirName): void
    {
        if ($slash === null) {
            $this->assertEquals($outputDirName, $this->dir->addEndSlash($inputDirName));
        } else {
            $this->assertEquals($outputDirName, $this->dir->addEndSlash($inputDirName, $slash));
        }
    }

    /**
     * @dataProvider getRemoveEndSlashData
     */
    public function testRemoveEndSlash(string $inputDirName, ?string $slash, string $outputDirName): void
    {
        if ($slash === null) {
            $this->assertEquals($outputDirName, $this->dir->removeEndSlash($inputDirName));
        } else {
            $this->assertEquals($outputDirName, $this->dir->removeEndSlash($inputDirName, $slash));
        }
    }

    /**
     * @dataProvider getIsWriteableData
     */
    public function testIsWriteable(string $dir, string $exists, array $dontExists): void
    {
        /** @var FileService|ObjectProphecy $file */
        $file = $this->prophesize(FileService::class);

        foreach ($dontExists as $dontExist) {
            $file->exists($dontExist)
                ->willReturn(false)
                ->shouldBeCalledOnce()
            ;
        }

        $file->exists($exists ?: DIRECTORY_SEPARATOR)
            ->willReturn(true)
            ->shouldBeCalledOnce()
        ;

        $file->isWritable($exists ?: DIRECTORY_SEPARATOR, true)
            ->willReturn(true)
            ->shouldBeCalledOnce()
        ;

        $this->dir->isWritable($dir, $file->reveal());
    }

    /**
     * @dataProvider getEscapeForGlobData
     */
    public function testEscapeForGlob(string $input, string $output): void
    {
        $this->assertEquals($output, $this->dir->escapeForGlob($input));
    }

    public function getAddEndSlashData(): array
    {
        return [
            ['herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold', null, 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold' . DIRECTORY_SEPARATOR],
            [DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold', null, '' . DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold' . DIRECTORY_SEPARATOR],
            [DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold' . DIRECTORY_SEPARATOR, null, '' . DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold' . DIRECTORY_SEPARATOR],
            ['', null, DIRECTORY_SEPARATOR],
            ['', '-_-', '-_-'],
            ['herz-_-aus-_-gold', '-_-', 'herz-_-aus-_-gold-_-'],
            ['-_-herz-_-aus-_-gold', '-_-', '-_-herz-_-aus-_-gold-_-'],
            ['-_-herz-_-aus-_-gold-_-', '-_-', '-_-herz-_-aus-_-gold-_-'],
        ];
    }

    public function getRemoveEndSlashData(): array
    {
        return [
            ['herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold', null, 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold'],
            [DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold', null, '' . DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold'],
            [DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold' . DIRECTORY_SEPARATOR, null, '' . DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'gold'],
            ['', null, ''],
            ['', '-_-', ''],
            [DIRECTORY_SEPARATOR, null, DIRECTORY_SEPARATOR],
            ['-_-', '-_-', '-_-'],
            ['herz-_-aus-_-gold', '-_-', 'herz-_-aus-_-gold'],
            ['-_-herz-_-aus-_-gold', '-_-', '-_-herz-_-aus-_-gold'],
            ['-_-herz-_-aus-_-gold-_-', '-_-', '-_-herz-_-aus-_-gold'],
        ];
    }

    public function getIsWriteableData(): array
    {
        return [
            [
                DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus',
                DIRECTORY_SEPARATOR,
                [
                    DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR,
                    DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR,
                ],
            ],
            [
                DIRECTORY_SEPARATOR . 'herz',
                DIRECTORY_SEPARATOR,
                [DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR],
            ],
            [
                DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'Gold',
                DIRECTORY_SEPARATOR,
                [
                    DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR,
                    DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR,
                    DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'Gold' . DIRECTORY_SEPARATOR,
                ],
            ],
            [
                DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'Gold',
                DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR,
                [
                    DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR,
                    DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'Gold' . DIRECTORY_SEPARATOR,
                ],
            ],
            [
                DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                [
                    DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR,
                    DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR,
                ],
            ],
            [
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                [],
            ],
            [
                '',
                DIRECTORY_SEPARATOR,
                [],
            ],
            [
                DIRECTORY_SEPARATOR,
                '',
                [
                    DIRECTORY_SEPARATOR,
                ],
            ],
            [
                DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'Gold',
                DIRECTORY_SEPARATOR . 'herz' . DIRECTORY_SEPARATOR . 'aus' . DIRECTORY_SEPARATOR . 'Gold' . DIRECTORY_SEPARATOR,
                [],
            ],
        ];
    }

    public function getEscapeForGlobData(): array
    {
        return [
            ['/herz/aus/Gold', '/herz/aus/Gold'],
            ['\herz\aus\Gold', '\herz\aus\Gold'],
            ['/herz/aus/Gold/*42*', '/herz/aus/Gold/[*]42[*]'],
            ['\herz\aus\Gold\*42*', '\herz\aus\Gold\[*]42[*]'],
            ['/herz/aus/Gold/?42?', '/herz/aus/Gold/[?]42[?]'],
            ['\herz\aus\Gold\?42?', '\herz\aus\Gold\[?]42[?]'],
            ['/herz/aus/Gold/[42]', '/herz/aus/Gold/[[]42]'],
            ['\herz\aus\Gold\[42]', '\herz\aus\Gold\[[]42]'],
            ['*', '[*]'],
            ['?', '[?]'],
            ['[', '[[]'],
        ];
    }
}
