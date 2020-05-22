<?php declare(strict_types=1);

namespace Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Dto\Ffmpeg\Media;
use GibsonOS\Core\Dto\Image as ImageDto;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\FfmpegService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Mock\Dto\Ffmpeg\Media as MediaMock;
use Prophecy\Argument;
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
     * @var FfmpegService
     */
    private $ffmpeg;

    /**
     * @var ObjectProphecy|DateTimeService
     */
    private $dateTime;

    /**
     * @var ObjectProphecy|FileService
     */
    private $file;

    /**
     * @var ObjectProphecy|ProcessService
     */
    private $process;

    /**
     * @var ObjectProphecy|ImageService
     */
    private $image;

    /**
     * @var string
     */
    private $inputVideoFilename;

    /**
     * @var string
     */
    private $outputVideoFilename;

    /**
     * @var string
     */
    private $logFilename;

    /**
     * @var string
     */
    private $logPath;

    /**
     * @var string
     */
    private $defaultCommand;

    protected function _before()
    {
        $this->ffmpegPath = 'path/to/ffmpeg';
        $this->dateTime = $this->prophesize(DateTimeService::class);
        $this->file = $this->prophesize(FileService::class);
        $this->process = $this->prophesize(ProcessService::class);
        $this->image = $this->prophesize(ImageService::class);
        $this->inputVideoFilename = '/name/from/file.vid';
        $this->outputVideoFilename = '/name/to/file.vid';
        $this->logFilename = 'ffmpegfile.vid';
        $this->logPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->logFilename;
        $this->defaultCommand = sprintf(
            '%s -i %s %s > %s 2> %s',
            $this->ffmpegPath,
            escapeshellarg($this->inputVideoFilename),
            escapeshellarg($this->outputVideoFilename),
            escapeshellarg($this->logPath),
            escapeshellarg($this->logPath)
        );

        $this->ffmpeg = new FfmpegService(
            $this->ffmpegPath,
            $this->dateTime->reveal(),
            $this->file->reveal(),
            $this->process->reveal(),
            $this->image->reveal()
        );
    }

    protected function _after()
    {
    }

    /**
     * @dataProvider getMetaDataStrings
     *
     * @throws FileNotFound
     * @throws ProcessError
     */
    public function testGetFileMetaDataString(string $filename, string $return): void
    {
        $file = $this->initTestGetFileMetaDataString($filename);

        $this->assertEquals($return, $this->ffmpeg->getFileMetaDataString($this->inputVideoFilename));
        fclose($file);
    }

    /**
     * @dataProvider getMetaDataStrings
     *
     * @throws FileNotFound
     * @throws ProcessError
     */
    public function testGetFileMetaDataStringFileDoesntExists(string $filename): void
    {
        $this->expectException(FileNotFound::class);

        $file = $this->initTestGetFileMetaDataString($filename);
        $this->file->exists($this->inputVideoFilename)
            ->willReturn(false)
        ;
        $this->file->isReadable($this->inputVideoFilename)
            ->shouldNotBeCalled()
        ;
        $this->process->open($this->ffmpegPath . ' -i ' . escapeshellarg($this->inputVideoFilename), 'r')
            ->shouldNotBeCalled()
        ;
        $this->process->close($file)
            ->shouldNotBeCalled()
        ;
        fclose($file);

        $this->ffmpeg->getFileMetaDataString($this->inputVideoFilename);
    }

    /**
     * @dataProvider getMetaDataStrings
     *
     * @throws FileNotFound
     * @throws ProcessError
     */
    public function testGetFileMetaDataStringFileIsNotReadable(string $filename): void
    {
        $this->expectException(FileNotFound::class);

        $file = $this->initTestGetFileMetaDataString($filename);
        $this->file->isReadable($this->inputVideoFilename)
            ->willReturn(false)
        ;
        $this->process->open($this->ffmpegPath . ' -i ' . escapeshellarg($this->inputVideoFilename), 'r')
            ->shouldNotBeCalled()
        ;
        $this->process->close($file)
            ->shouldNotBeCalled()
        ;
        fclose($file);

        $this->ffmpeg->getFileMetaDataString($this->inputVideoFilename);
    }

    public function testConvert(): void
    {
        $media = $this->initTestConvert();

        $this->ffmpeg->convert($media, $this->outputVideoFilename);
    }

    public function testConvertFileDoesntExists(): void
    {
        $this->expectException(FileNotFound::class);

        $media = $this->initTestConvert();
        $this->file->exists($this->outputVideoFilename)
            ->willReturn(false)
        ;

        $this->ffmpeg->convert($media, $this->outputVideoFilename);
    }

    public function testConvertVideoCodecWithSelectedVideoStreamId(): void
    {
        $media = $this->initTestConvert();
        $media->selectVideoStream('v1');

        $this->process->execute($this->defaultCommand)
            ->shouldNotBeCalled()
        ;
        $this->process->execute(sprintf(
            '%s -i %s -map v1 -c:v "codec" -sn %s > %s 2> %s',
            $this->ffmpegPath,
            escapeshellarg($this->inputVideoFilename),
            escapeshellarg($this->outputVideoFilename),
            escapeshellarg($this->logPath),
            escapeshellarg($this->logPath)
        ))
            ->shouldBeCalledOnce()
        ;

        $this->ffmpeg->convert($media, $this->outputVideoFilename, 'codec');
    }

    public function testConvertVideoCodecWithoutSelectedVideoStreamId(): void
    {
        $media = $this->initTestConvert();

        $this->ffmpeg->convert($media, $this->outputVideoFilename, 'codec');
    }

    public function testConvertAudioCodecWithSelectedAudioStreamId(): void
    {
        $media = $this->initTestConvert();
        $media->selectAudioStream('a3');

        $this->process->execute($this->defaultCommand)
            ->shouldNotBeCalled()
        ;
        $this->process->execute(sprintf(
            '%s -i %s -map a3 -c:a "codec" %s > %s 2> %s',
            $this->ffmpegPath,
            escapeshellarg($this->inputVideoFilename),
            escapeshellarg($this->outputVideoFilename),
            escapeshellarg($this->logPath),
            escapeshellarg($this->logPath)
        ))
            ->shouldBeCalledOnce()
        ;

        $this->ffmpeg->convert($media, $this->outputVideoFilename, null, 'codec');
    }

    public function testConvertAudioCodecWithoutSelectedAudioStreamId(): void
    {
        $media = $this->initTestConvert();

        $this->ffmpeg->convert($media, $this->outputVideoFilename, null, 'codec');
    }

    public function testConvertWithSelectedSubtitleStreamId(): void
    {
        $media = $this->initTestConvert();
        $media->selectSubtitleStream('s4');

        $this->ffmpeg->convert($media, $this->outputVideoFilename);
    }

    public function testConvertWithSelectedVideoStreamIdAndSubtitleStreamId(): void
    {
        $media = $this->initTestConvert();
        $media->selectVideoStream('v2');
        $media->selectSubtitleStream('s4');

        $this->process->execute($this->defaultCommand)
            ->shouldNotBeCalled()
        ;
        $this->process->execute(sprintf(
            '%s -i %s -map v2 -c:v "codec" -vf subtitles=%s:si=3 %s > %s 2> %s',
            $this->ffmpegPath,
            escapeshellarg($this->inputVideoFilename),
            escapeshellarg($this->inputVideoFilename),
            escapeshellarg($this->outputVideoFilename),
            escapeshellarg($this->logPath),
            escapeshellarg($this->logPath)
        ))
            ->shouldBeCalledOnce()
        ;

        $this->ffmpeg->convert($media, $this->outputVideoFilename, 'codec');
    }

    public function testConvertWithOptions(): void
    {
        $media = $this->initTestConvert();

        $this->process->execute($this->defaultCommand)
            ->shouldNotBeCalled()
        ;
        $this->process->execute(sprintf(
            '%s -i %s -arthur "dent" -mouse "answer" %s > %s 2> %s',
            $this->ffmpegPath,
            escapeshellarg($this->inputVideoFilename),
            escapeshellarg($this->outputVideoFilename),
            escapeshellarg($this->logPath),
            escapeshellarg($this->logPath)
        ))
            ->shouldBeCalledOnce()
        ;

        $this->ffmpeg->convert($media, $this->outputVideoFilename, null, null, [
            'arthur' => 'dent',
            'mouse' => 'answer',
        ]);
    }

    /**
     * @dataProvider getConvertStatusStrings
     */
    public function testGetConvertStatus(
        string $lastLine,
        int $frame,
        float $fps,
        float $quality,
        int $size,
        string $time,
        float $bitrate,
        int $hours,
        int $minutes,
        int $seconds,
        int $microseconds
    ): void {
        $this->file->exists(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ffmpeg' . $this->logFilename)
            ->willReturn(true)
            ->shouldBeCalledOnce()
        ;
        $this->file->readLastLine(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ffmpeg' . $this->logFilename)
            ->willReturn($lastLine)
            ->shouldBeCalledOnce()
        ;

        $this->dateTime->get($time)
            ->willReturn(new \DateTime($time))
            ->shouldBeCalledOnce()
        ;

        $convertStatus = $this->ffmpeg->getConvertStatus($this->logFilename);

        $this->assertEquals($frame, $convertStatus->getFrame(), 'Frame');
        $this->assertEquals($fps, $convertStatus->getFps(), 'FPS');
        $this->assertEquals($quality, $convertStatus->getQuality(), 'Quality');
        $this->assertEquals($size, $convertStatus->getSize(), 'Size');
        $this->assertEquals($bitrate, $convertStatus->getBitrate(), 'Bitrate');
        $this->assertEquals($hours, (int) $convertStatus->getTime()->format('G'), 'Hours');
        $this->assertEquals($minutes, (int) $convertStatus->getTime()->format('i'), 'Minutes');
        $this->assertEquals($seconds, (int) $convertStatus->getTime()->format('s'), 'Seconds');
        $this->assertEquals($microseconds, (int) $convertStatus->getTime()->format('v'), 'Seconds');
    }

    public function testGetConvertStatusFileDoesntExists(): void
    {
        $this->expectException(FileNotFound::class);

        $this->file->exists(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ffmpeg' . $this->logFilename)
            ->willReturn(false)
            ->shouldBeCalledOnce()
        ;

        $this->ffmpeg->getConvertStatus($this->logFilename);
    }

    public function testGetConvertStatusReadLastLineError(): void
    {
        $this->expectException(ConvertStatusError::class);

        $this->file->exists(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ffmpeg' . $this->logFilename)
            ->willReturn(true)
            ->shouldBeCalledOnce()
        ;
        $this->file->readLastLine(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ffmpeg' . $this->logFilename)
            ->willReturn('Unwarscheinlichkeits Drive')
            ->shouldBeCalledOnce()
        ;

        $this->ffmpeg->getConvertStatus($this->logFilename);
    }

    public function testGetImageByFrame(): void
    {
        $frameNumber = '00:17:03.23';

        $this->process->execute(Argument::containingString(sprintf(
            '%s -ss %s -i %s -an -r 1 -vframes 1 -f image2 %s',
            $this->ffmpegPath,
            $frameNumber,
            escapeshellarg($this->inputVideoFilename),
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tmpFrame'
        )))
            ->shouldBeCalledOnce()
        ;

        $image = new ImageDto(imagecreate(1, 1));
        $this->image->load(Argument::containingString(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tmpFrame'))
            ->willReturn($image)
            ->shouldBeCalledOnce()
        ;

        $this->assertSame($image, $this->ffmpeg->getImageByFrame($this->inputVideoFilename, $frameNumber));
    }

    public function getMetaDataStrings(): array
    {
        $mockDir =
            __DIR__ . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'mock' . DIRECTORY_SEPARATOR .
            'Service' . DIRECTORY_SEPARATOR .
            'Ffmpeg' . DIRECTORY_SEPARATOR
        ;

        return [
            [
                $mockDir . 'avi1.txt',
                'Input #0, avi, from \'JKhkjaüäasmjklß.avi\':
  Metadata:
    encoder         : VirtualDubMod 1.5.10.2 (build 2540/release)
  Duration: 00:10:41.76, start: 0.000000, bitrate: 1632 kb/s
    Stream #0:0: Video: mpeg4 (Advanced Simple Profile) (XVID / 0x44495658), yuv420p, 640x480 [SAR 1:1 DAR 4:3], 1491 kb/s, 25 fps, 25 tbr, 25 tbn, 25 tbc
    Stream #0:1: Audio: mp3 (U[0][0][0] / 0x0055), 44100 Hz, stereo, s16p, 128 kb/s
At least one output file must be specified
',
            ],
            [
                $mockDir . 'avi2.txt',
                'Input #0, avi, from \'dfsdf.öad22x3.avi\':
  Metadata:
    encoder         : VirtualDubMod 1.5.4.1 (build 2178/release)
  Duration: 00:10:40.00, start: 0.000000, bitrate: 1664 kb/s
    Stream #0:0: Video: mpeg4 (Advanced Simple Profile) (XVID / 0x44495658), yuv420p, 640x480 [SAR 1:1 DAR 4:3], 1526 kb/s, 25 fps, 25 tbr, 25 tbn, 25 tbc
    Stream #0:1: Audio: mp3 (U[0][0][0] / 0x0055), 48000 Hz, stereo, s16p, 128 kb/s
At least one output file must be specified
',
            ],
            [
                $mockDir . 'mkv1.txt',
                'Input #0, matroska,webm, from \'Pokemon.-.Staffel.01.-.IL46.-.Folge.046.-.Angreifer.aus.der.Urzeit.mkv\':
  Metadata:
    encoder         : libebml v0.7.7 + libmatroska v0.8.1
    creation_time   : 2008-12-04T23:13:36.000000Z
  Duration: 00:21:41.50, start: 0.000000, bitrate: 1227 kb/s
    Chapter #0:0: start 0.000000, end 60.967000
    Metadata:
      title           : Chapter 1
    Chapter #0:1: start 60.967000, end 562.033000
    Metadata:
      title           : Chapter 2
    Chapter #0:2: start 562.033000, end 1236.600000
    Metadata:
      title           : Chapter 3
    Chapter #0:3: start 1236.600000, end 1301.504000
    Metadata:
      title           : Chapter 4
    Stream #0:0(jpn): Video: h264 (High), yuv420p(progressive), 688x480 [SAR 47:51 DAR 2021:1530], SAR 199:216 DAR 8557:6480, 29.97 fps, 29.97 tbr, 1k tbn, 59.94 tbc (default)
    Metadata:
      title           : NTSC
    Stream #0:1(ger): Audio: aac (HE-AACv2), 48000 Hz, stereo, fltp (default)
    Metadata:
      title           : 2.0
    Stream #0:2(eng): Audio: aac (HE-AAC), 48000 Hz, stereo, fltp
    Metadata:
      title           : English
At least one output file must be specified
',
            ],
            [
                $mockDir . 'mkv2.txt',
                'Input #0, matroska,webm, from \'Fresh.Off.the.Boat.S01.GERMAN.720p.HDTV.X264-WiSHTV - serienjunkies.org/Fresh.Off.the.Boat.S01E01.GERMAN.720p.HDTV.X264-WiSHTV/wtv-freshofftheboat-s01e01-720p.mkv\':
  Metadata:
    encoder         : libebml v1.3.3 + libmatroska v1.4.4
    creation_time   : 2016-06-01T20:34:21.000000Z
  Duration: 00:20:33.98, start: 0.000000, bitrate: 2816 kb/s
    Stream #0:0: Video: h264 (High), yuv420p(tv, bt709, progressive), 1280x720 [SAR 1:1 DAR 16:9], 25 fps, 25 tbr, 1k tbn, 50 tbc (default)
    Metadata:
      BPS             : 2430971
      BPS-eng         : 2430971
      DURATION        : 00:20:33.960000000
      DURATION-eng    : 00:20:33.960000000
      NUMBER_OF_FRAMES: 30849
      NUMBER_OF_FRAMES-eng: 30849
      NUMBER_OF_BYTES : 374965215
      NUMBER_OF_BYTES-eng: 374965215
      _STATISTICS_WRITING_APP: mkvmerge v9.0.0 (\'Power to progress\') 64bit
      _STATISTICS_WRITING_APP-eng: mkvmerge v9.0.0 (\'Power to progress\') 64bit
      _STATISTICS_WRITING_DATE_UTC: 2016-06-01 20:34:21
      _STATISTICS_WRITING_DATE_UTC-eng: 2016-06-01 20:34:21
      _STATISTICS_TAGS: BPS DURATION NUMBER_OF_FRAMES NUMBER_OF_BYTES
      _STATISTICS_TAGS-eng: BPS DURATION NUMBER_OF_FRAMES NUMBER_OF_BYTES
    Stream #0:1(ger): Audio: ac3, 48000 Hz, stereo, fltp, 384 kb/s (default)
    Metadata:
      BPS             : 384000
      BPS-eng         : 384000
      DURATION        : 00:20:33.984000000
      DURATION-eng    : 00:20:33.984000000
      NUMBER_OF_FRAMES: 38562
      NUMBER_OF_FRAMES-eng: 38562
      NUMBER_OF_BYTES : 59231232
      NUMBER_OF_BYTES-eng: 59231232
      _STATISTICS_WRITING_APP: mkvmerge v9.0.0 (\'Power to progress\') 64bit
      _STATISTICS_WRITING_APP-eng: mkvmerge v9.0.0 (\'Power to progress\') 64bit
      _STATISTICS_WRITING_DATE_UTC: 2016-06-01 20:34:21
      _STATISTICS_WRITING_DATE_UTC-eng: 2016-06-01 20:34:21
      _STATISTICS_TAGS: BPS DURATION NUMBER_OF_FRAMES NUMBER_OF_BYTES
      _STATISTICS_TAGS-eng: BPS DURATION NUMBER_OF_FRAMES NUMBER_OF_BYTES
At least one output file must be specified
',
            ],
            [
                $mockDir . 'mkv3.txt',
                'Input #0, matroska,webm, from \'MF S03 720p TVS - serienjunkies.org/Modern.Family.S03E01.Experiment.Touristenranch.German.DD51.Dubbed.DL.720p.BD.x264-TVS/tvs-mf-dd51-ded-dl-7p-bd-x264-301.mkv\':
  Metadata:
    encoder         : libebml v1.3.0 + libmatroska v1.4.0
    creation_time   : 2013-10-27T13:57:15.000000Z
  Duration: 00:20:46.14, start: 0.000000, bitrate: 7378 kb/s
    Stream #0:0: Video: h264 (High), yuv420p(progressive), 1280x720 [SAR 1:1 DAR 16:9], 25 fps, 25 tbr, 1k tbn, 47.95 tbc (default)
    Stream #0:1(ger): Audio: ac3, 48000 Hz, 5.1(side), fltp, 448 kb/s (default)
    Stream #0:2(eng): Audio: ac3, 48000 Hz, 5.1(side), fltp, 448 kb/s
At least one output file must be specified
',
            ],
            [
                $mockDir . 'mkv4.txt',
                'Input #0, matroska,webm, from \'Modern.Family.S10.German.DD51.Dubbed.DL.720p.AmazonHD.AVC-TVS - serienJK/Modern.Family.S10E01.Nichts.geht.ueber.eine.Parade.German.DD51.Dubbed.DL.720p.AmazonHD.AVC-TVS/tvs-mf-dd51-ded-dl-7p-azhd-avc-1001.mkv\':
  Metadata:
    encoder         : libebml v1.3.7 + libmatroska v1.5.0
    creation_time   : 2019-06-20T19:49:17.000000Z
  Duration: 00:20:39.62, start: 0.000000, bitrate: 3887 kb/s
    Stream #0:0: Video: h264 (High), yuv420p(tv, bt709, progressive), 1280x720 [SAR 1:1 DAR 16:9], 25 fps, 25 tbr, 1k tbn, 50 tbc (default)
    Metadata:
      BPS-eng         : 2989259
      DURATION-eng    : 00:20:39.600000000
      NUMBER_OF_FRAMES-eng: 30990
      NUMBER_OF_BYTES-eng: 463185716
      _STATISTICS_WRITING_APP-eng: mkvmerge v33.1.0 (\'Primrose\') 64-bit
      _STATISTICS_WRITING_DATE_UTC-eng: 2019-06-20 19:49:17
      _STATISTICS_TAGS-eng: BPS DURATION NUMBER_OF_FRAMES NUMBER_OF_BYTES
    Stream #0:1(ger): Audio: ac3, 48000 Hz, 5.1(side), fltp, 448 kb/s (default)
    Metadata:
      BPS-eng         : 448000
      DURATION-eng    : 00:20:39.616000000
      NUMBER_OF_FRAMES-eng: 38738
      NUMBER_OF_BYTES-eng: 69418496
      _STATISTICS_WRITING_APP-eng: mkvmerge v33.1.0 (\'Primrose\') 64-bit
      _STATISTICS_WRITING_DATE_UTC-eng: 2019-06-20 19:49:17
      _STATISTICS_TAGS-eng: BPS DURATION NUMBER_OF_FRAMES NUMBER_OF_BYTES
    Stream #0:2(eng): Audio: ac3, 48000 Hz, 5.1(side), fltp, 448 kb/s
    Metadata:
      BPS-eng         : 448000
      DURATION-eng    : 00:20:39.584000000
      NUMBER_OF_FRAMES-eng: 38737
      NUMBER_OF_BYTES-eng: 69416704
      _STATISTICS_WRITING_APP-eng: mkvmerge v33.1.0 (\'Primrose\') 64-bit
      _STATISTICS_WRITING_DATE_UTC-eng: 2019-06-20 19:49:17
      _STATISTICS_TAGS-eng: BPS DURATION NUMBER_OF_FRAMES NUMBER_OF_BYTES
At least one output file must be specified
',
            ],
            [
                $mockDir . 'mp41.txt',
                'Input #0, mov,mp4,m4a,3gp,3g2,mj2, from \'Harry.Potter.(08).-.und.die.Heiligtümer.des.Todes.2.2011.German.AAC51.DL.720p.BluRay.x264-Kristallprinz.mp4\':
  Metadata:
    major_brand     : mp42
    minor_version   : 512
    compatible_brands: isomiso2avc1mp41
    creation_time   : 2016-01-22T20:50:02.000000Z
    title           : Harry Potter und die Heiligtümer des Todes - Teil 2
    comment         : tagged by my own Software
    genre           : Fantasy
    date            : 2011-07-15T10:00:00Z
    sort_name       : Harry Potter (08)
    description     : Es endet alles hier.
    synopsis        : Das Ende ist nah! Hogwarts hat als Zuflucht ausgedient, Voldemorts Schergen haben die Macht über ganz England an sich gerissen. Harry, Ron und Hermine sind auf der Flucht, die Lage scheint aussichtslos. Eine letzte Chance bleibt dem Trio noch, das Blatt 
    encoder         : by Kristallprinz
    hd_video        : 1
    media_type      : 9
    rating          : 0
    gapless_playback: 0
    iTunEXTC        : de-movies|Ab 12 Jahren|200|
    iTunMOVI        : <?xml version="1.0" encoding="UTF-8"?>
                    : <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
                    : <plist version="1.0">
                    : <dict>
                    : 	<key>cast</key>
                    : 	<array>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Daniel Radcliffe</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Rupert Grint</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Emma Watson</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Alan Rickman</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Maggie Smith</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Helena Bonham Carter</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Emma Thompson</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Robbie Coltrane</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ralph Fiennes</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Gemma Jones</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Clémence Poésy</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Warwick Davis</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Domhnall Gleeson</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>James Phelps</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Oliver Phelps</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Jim Broadbent</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Mark Williams</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Pauline Stone</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>David Thewlis</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Suzie Toase</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Natalia Tena</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>George Harris</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ciarán Hinds</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Julie Walters</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>David Ryall</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Arben Bajraktaraj</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Peter Mullan</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>David Bradley</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Miriam Margolyes</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Timothy Spall</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Jason Isaacs</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ralph Ineson</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Helen McCrory</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Chris Rankin</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Tom Felton</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Rod Hunt</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Dave Legeno</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Nick Moran</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Guy Henry</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Bonnie Wright</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Evanna Lynch</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Anna Shaffer</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Matthew Lewis</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Devon Murray</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Alfie Enoch</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Jessie Cave</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Shefali Chowdhury</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Afshan Azad</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Louis Cordice</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Josh Herdman</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Scarlett Byrne</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Isabella Laughland</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Jamie Marks</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Katie Leung</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Georgina Leonidas</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Freddie Stroma</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>John Hurt</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Kelly Macdonald</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Michael Gambon</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Gary Oldman</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Adrian Rawlins</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Geraldine Somerville</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Anthony Allgood</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Rusty Goffe</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Benn Northover</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ian Peck</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Hebe Beardsall</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>William Melling</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Sian Grace Phillips</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Suzanne Toase</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Amber Evans</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ruby Evans</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Jon Key</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Philip Wright</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Gary Sayer</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Tony Adkins</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Penelope McGhie</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ellie Darcey-Alden</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ariella Paradise</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Benedict Clarke</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Alfie McIlwain</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Rohan Gotobed</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Toby Papworth</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Peter G. Reed</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Judith Sharp</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Emil Hostina</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Bob Yves Van Hellenberg Hubar</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Granville Saxton</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Tony Kirwood</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ashley McGuire</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Arthur Bowen</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Daphne de Beistegui</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Will Dunn</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Jade Gordon</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Bertie Gilbert</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Helena Barlow</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ryan Turner</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Jamie Campbell Bower</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Luke Newberry</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Sean Biggerstaff</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Leslie Phillips</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Graham Duff</string>
                    : 		</dict>
                    : 	</array>
                    : 	<key>directors</key>
                    : 	<array>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>David Yates</string>
                    : 		</dict>
                    : 	</array>
                    : 	<key>producers</key>
                    : 	<array>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Alexandre Desplat</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>David Barron</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Mark Day</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Fiona Weir</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Stuart Craig</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Eduardo Serra</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Stewart Alves</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Andy Hass</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Howard R. Campbell</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Yannick Honore</string>
                    : 		</dict>
                    : 	</array>
                    : 	<key>screenwriters</key>
                    : 	<array>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Steve Kloves</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>J. K. Rowling</string>
                    : 		</dict>
                    : 	</array>
                    : 	<key>studio</key>
                    : 	<string>Kristallprinz</string>
                    : </dict>
                    : </plist>
                    : 
  Duration: 02:10:26.90, start: 0.000000, bitrate: 3602 kb/s
    Chapter #0:0: start 0.000000, end 178.261000
    Metadata:
      title           : 00:00:00.000
    Chapter #0:1: start 178.261000, end 381.256000
    Metadata:
      title           : 00:02:58.220
    Chapter #0:2: start 381.256000, end 621.955000
    Metadata:
      title           : 00:06:21.172
    Chapter #0:3: start 621.955000, end 868.034000
    Metadata:
      title           : 00:10:21.871
    Chapter #0:4: start 868.034000, end 1205.663000
    Metadata:
      title           : 00:14:27.992
    Chapter #0:5: start 1205.663000, end 1367.825000
    Metadata:
      title           : 00:20:05.621
    Chapter #0:6: start 1367.825000, end 1538.704000
    Metadata:
      title           : 00:22:47.783
    Chapter #0:7: start 1538.704000, end 1846.887000
    Metadata:
      title           : 00:25:38.620
    Chapter #0:8: start 1846.887000, end 2073.697000
    Metadata:
      title           : 00:30:46.803
    Chapter #0:9: start 2073.697000, end 2432.305000
    Metadata:
      title           : 00:34:33.655
    Chapter #0:10: start 2432.305000, end 2752.333000
    Metadata:
      title           : 00:40:32.263
    Chapter #0:11: start 2752.333000, end 3015.512000
    Metadata:
      title           : 00:45:52.249
    Chapter #0:12: start 3015.512000, end 3281.069000
    Metadata:
      title           : 00:50:15.471
    Chapter #0:13: start 3281.069000, end 3541.288000
    Metadata:
      title           : 00:54:41.027
    Chapter #0:14: start 3541.288000, end 3822.152000
    Metadata:
      title           : 00:59:01.246
    Chapter #0:15: start 3822.152000, end 4026.105000
    Metadata:
      title           : 01:03:42.068
    Chapter #0:16: start 4026.105000, end 4284.572000
    Metadata:
      title           : 01:07:06.063
    Chapter #0:17: start 4284.572000, end 4478.974000
    Metadata:
      title           : 01:11:24.488
    Chapter #0:18: start 4478.974000, end 5020.891000
    Metadata:
      title           : 01:14:38.932
    Chapter #0:19: start 5020.891000, end 5357.811000
    Metadata:
      title           : 01:23:40.849
    Chapter #0:20: start 5357.811000, end 5485.063000
    Metadata:
      title           : 01:29:17.727
    Chapter #0:21: start 5485.063000, end 5797.709000
    Metadata:
      title           : 01:31:25.021
    Chapter #0:22: start 5797.709000, end 6069.146000
    Metadata:
      title           : 01:36:37.666
    Chapter #0:23: start 6069.146000, end 6402.396000
    Metadata:
      title           : 01:41:09.104
    Chapter #0:24: start 6402.396000, end 6618.403000
    Metadata:
      title           : 01:46:42.312
    Chapter #0:25: start 6618.403000, end 6856.725000
    Metadata:
      title           : 01:50:18.361
    Chapter #0:26: start 6856.725000, end 7092.961000
    Metadata:
      title           : 01:54:16.683
    Chapter #0:27: start 7092.961000, end 7826.861000
    Metadata:
      title           : 01:58:12.877
    Stream #0:0(und): Video: h264 (Main) (avc1 / 0x31637661), yuv420p(tv, bt709), 1280x720, 2799 kb/s, 23.98 fps, 23.98 tbr, 90k tbn, 47.95 tbc (default)
    Metadata:
      creation_time   : 2016-01-22T20:50:02.000000Z
      handler_name    : VideoHandler
    Stream #0:1(deu): Audio: aac (LC) (mp4a / 0x6134706D), 48000 Hz, 5.1, fltp, 395 kb/s (default)
    Metadata:
      creation_time   : 2016-01-22T20:50:02.000000Z
      handler_name    : Surround
    Stream #0:2(eng): Audio: aac (LC) (mp4a / 0x6134706D), 48000 Hz, 5.1, fltp, 396 kb/s
    Metadata:
      creation_time   : 2016-01-22T20:50:02.000000Z
      handler_name    : Surround
    Stream #0:3(deu): Subtitle: mov_text (tx3g / 0x67337874), 1280x60 (default)
    Metadata:
      creation_time   : 2016-01-22T20:50:02.000000Z
      handler_name    : SubtitleHandler
    Stream #0:4(eng): Data: bin_data (text / 0x74786574)
    Metadata:
      creation_time   : 2016-01-22T20:50:02.000000Z
      handler_name    : SubtitleHandler
    Stream #0:5: Video: png, rgba(pc), 480x660, 90k tbr, 90k tbn, 90k tbc
At least one output file must be specified
',
            ],
            [
                $mockDir . 'mp42.txt',
                'Input #0, mov,mp4,m4a,3gp,3g2,mj2, from \'ModernFamily.07.SO - filecrypt.cc/S07E01.Im Sommer der leidenden Liebenden.mp4\':
  Metadata:
    major_brand     : mp42
    minor_version   : 512
    compatible_brands: isomiso2avc1mp41
    creation_time   : 2018-06-30T11:41:31.000000Z
    title           : Im Sommer der leidenden Liebenden
    artist          : Modern Family
    album_artist    : Modern Family
    album           : Modern Family, Season 7
    comment         : tagged by my own Software
    genre           : Comedy
    date            : 2015-09-23T10:00:00Z
    track           : 1/22
    show            : Modern Family
    network         : ABC (US)
    episode_id      : S07E01
    season_number   : 7
    episode_sort    : 1
    sort_show       : Modern Family
    description     : Haley erfährt, dass Andy Beth einen Antrag machen will. Sie versucht ihn aufzuhalten, kommt jedoch zu spät. Cam ist mit seinem arbeitslosen Mann, Mitchell, überfordert. Er bittet Mitchells Ex-Chef darum, ihn wieder einzustellen.
    synopsis        : Haley erfährt, dass Andy Beth einen Antrag machen will. Sie versucht ihn aufzuhalten, kommt jedoch zu spät. Cam ist mit seinem arbeitslosen Mann, Mitchell, überfordert. Er bittet Mitchells Ex-Chef darum, ihn wieder einzustellen.
    encoder         : by Kristallprinz
    hd_video        : 1
    media_type      : 10
    rating          : 0
    gapless_playback: 0
    account_id      : 
    iTunEXTC        : de-tv|Ab 16 Jahren|500|
    iTunMOVI        : <?xml version="1.0" encoding="UTF-8"?>
                    : <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
                    : <plist version="1.0">
                    : <dict>
                    : 	<key>cast</key>
                    : 	<array>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Julie Bowen</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ty Burrell</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Sofía Vergara</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Sarah Hyland</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ariel Winter</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Jesse Tyler Ferguson</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Eric Stonestreet</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Ed O\'Neill</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Aubrey Anderson-Emmons</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Rico Rodriguez</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Nolan Gould</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Jeremy Maguire</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Adam DeVine</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Reid Ewing</string>
                    : 		</dict>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Justin Kirk</string>
                    : 		</dict>
                    : 	</array>
                    : 	<key>directors</key>
                    : 	<array>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Jim Hensz</string>
                    : 		</dict>
                    : 	</array>
                    : 	<key>screenwriters</key>
                    : 	<array>
                    : 		<dict>
                    : 			<key>name</key>
                    : 			<string>Abraham Higginbotham</string>
                    : 		</dict>
                    : 	</array>
                    : 	<key>studio</key>
                    : 	<string>Kristallprinz</string>
                    : </dict>
                    : </plist>
                    : 
  Duration: 00:20:52.10, start: -0.001333, bitrate: 3565 kb/s
    Stream #0:0(eng): Video: h264 (Main) (avc1 / 0x31637661), yuv420p(tv, bt709), 1280x720 [SAR 1:1 DAR 16:9], 2799 kb/s, 25 fps, 25 tbr, 90k tbn, 50 tbc (default)
    Metadata:
      creation_time   : 2018-06-30T11:41:31.000000Z
      handler_name    : VideoHandler
    Stream #0:1(deu): Audio: aac (LC) (mp4a / 0x6134706D), 48000 Hz, 5.1, fltp, 392 kb/s (default)
    Metadata:
      creation_time   : 2018-06-30T11:41:31.000000Z
      handler_name    : Surround
    Stream #0:2(eng): Audio: aac (LC) (mp4a / 0x6134706D), 48000 Hz, 5.1, fltp, 378 kb/s
    Metadata:
      creation_time   : 2018-06-30T11:41:31.000000Z
      handler_name    : Surround
    Stream #0:3(deu): Subtitle: mov_text (tx3g / 0x67337874), 1280x108, 0 kb/s (default)
    Metadata:
      creation_time   : 2018-06-30T11:41:31.000000Z
      handler_name    : SubtitleHandler
    Stream #0:4(eng): Subtitle: mov_text (tx3g / 0x67337874), 1280x108, 0 kb/s
    Metadata:
      creation_time   : 2018-06-30T11:41:31.000000Z
      handler_name    : SubtitleHandler
    Stream #0:5: Video: png, rgba(pc), 960x1320 [SAR 5669:5669 DAR 8:11], 90k tbr, 90k tbn, 90k tbc
At least one output file must be specified
',
            ],
        ];
    }

    private function initTestGetFileMetaDataString(string $filename)
    {
        $this->file->exists($this->inputVideoFilename)
            ->willReturn(true)
            ->shouldBeCalledOnce()
        ;
        $this->file->isReadable($this->inputVideoFilename)
            ->willReturn(true)
            ->shouldBeCalledOnce()
        ;

        $file = fopen($filename, 'r');
        $this->process->open($this->ffmpegPath . ' -i ' . escapeshellarg($this->inputVideoFilename), 'r')
            ->willReturn($file)
            ->shouldBeCalledOnce()
        ;
        $this->process->close($file)
            ->shouldBeCalledOnce()
        ;

        return $file;
    }

    public function getConvertStatusStrings(): array
    {
        return [
            [
                'frame=19858 fps= 67 q=29.0 size=   82377kB time=00:11:02.91 bitrate=1018.0kbits/s speed=2.23x    ',
                19858,
                67.0,
                29.0,
                82377,
                '00:11:02.91',
                1018.0,
                0,
                11,
                2,
                910,
            ],
            [
                'frame=   78 fps=7.1 q=29.0 size=     218kB time=00:00:02.98 bitrate= 597.9kbits/s speed=0.272x   ',
                78,
                7,
                29.0,
                218,
                '00:00:02.98',
                597.9,
                0,
                0,
                2,
                980,
            ],
            [
                'frame=32195 fps= 65 q=29.0 size=  137564kB time=01:17:54.51 bitrate=1048.8kbits/s speed=2.16x    ',
                32195,
                65,
                29.0,
                137564,
                '01:17:54.51',
                1048.8,
                1,
                17,
                54,
                510,
            ],
            [
                'frame=38515 fps= 65 q=29.0 size=  160858kB time=00:21:25.02 bitrate=1025.2kbits/s speed=2.18x    ',
                38515,
                65,
                29.0,
                160858,
                '00:21:25.02',
                1025.2,
                0,
                21,
                25,
                20,
            ],
        ];
    }

    private function initTestConvert($command = null): Media
    {
        $this->file->getFilename($this->outputVideoFilename)
            ->willReturn('file.vid')
            ->shouldBeCalledOnce()
        ;
        $this->file->exists($this->outputVideoFilename)
            ->willReturn(true)
            ->shouldBeCalledOnce()
        ;
        $this->file->delete(sys_get_temp_dir(), $this->logFilename)
            ->shouldBeCalledOnce()
        ;

        $this->process->execute($this->defaultCommand)
            ->shouldBeCalledOnce()
        ;

        return MediaMock::create($this->inputVideoFilename);
    }
}
