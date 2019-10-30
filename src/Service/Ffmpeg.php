<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Dto\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Dto\Ffmpeg\Media;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Factory\Image as ImageFactory;

class Ffmpeg extends AbstractService
{
    /**
     * @var string
     */
    public $ffpmegPath;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var File
     */
    private $file;

    /**
     * @var Process
     */
    private $process;

    /**
     * Ffmpeg constructor.
     *
     * @param string   $ffpmegPath
     * @param DateTime $dateTime
     * @param File     $file
     * @param Process  $process
     */
    public function __construct(string $ffpmegPath, DateTime $dateTime, File $file, Process $process)
    {
        $this->ffpmegPath = $ffpmegPath;
        $this->dateTime = $dateTime;
        $this->file = $file;
        $this->process = $process;
    }

    /**
     * @param string $filename
     *
     * @throws FileNotFound
     * @throws ProcessError
     *
     * @return string
     */
    public function getFileMetaDataString(string $filename): string
    {
        if (
            !$this->file->exists($filename) ||
            !$this->file->isReadable($filename)
        ) {
            throw new FileNotFound(sprintf('Datei %s existiert nicht!', $filename));
        }

        $ffMpeg = $this->process->open($this->ffpmegPath . ' -i ' . escapeshellarg($filename), 'r');
        $output = '';

        while ($out = fgets($ffMpeg)) {
            if (mb_strpos($out, 'Input') !== 0) {
                continue;
            }

            $output .= $out;

            while ($out = fgets($ffMpeg)) {
                $output .= $out;
            }
        }

        $this->process->close($ffMpeg);

        return $output;
    }

    /**
     * @param Media       $media
     * @param string      $outputFilename
     * @param string|null $videoCodec
     * @param string|null $audioCodec
     * @param array       $options
     *
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     */
    public function convert(
        Media $media,
        string $outputFilename,
        string $videoCodec = null,
        string $audioCodec = null,
        array $options = []
    ) {
        $optionString = '-i ' . escapeshellarg($media->getFilename()) . ' ';

        if (
            $audioCodec !== null &&
            $media->getSelectedAudioStreamId() !== null
        ) {
            $optionString .=
                '-map ' . $media->getSelectedAudioStreamId() . ' ' .
                '-c:v ' . escapeshellarg((string) $videoCodec) . ' ';
        }

        if (
            $videoCodec !== null &&
            $media->getSelectedVideoStreamId() !== null
        ) {
            $optionString .=
                '-map ' . $media->getSelectedVideoStreamId() . ' ' .
                '-c:a ' . escapeshellarg((string) $audioCodec) . ' ';

            if ($media->getSelectedSubtitleStreamId() !== null) {
                $subtitleStreamIds = array_keys($media->getSubtitleStreams());
                $optionString .=
                    '-vf subtitles=' . escapeshellarg($media->getFilename()) .
                    ':si=' . array_search($media->getSelectedSubtitleStreamId(), $subtitleStreamIds) . ' ';
            }
        }

        foreach ($options as $key => $option) {
            $optionString .= '-' . $key . ' ' . escapeshellarg($option) . ' ';
        }

        $filename = 'ffmpeg' . $this->file->getFilename($outputFilename);
        $logPath = sys_get_temp_dir() . '/' . $filename;
        $this->execute(
            $optionString .
            escapeshellarg($outputFilename) .
            ' > ' . escapeshellarg($logPath) .
            ' 2> ' . escapeshellarg($logPath)
        );

        if (!file_exists($outputFilename)) {
            throw new FileNotFound('Konvertieren war nicht erfolgreich!');
        }

        $this->file->delete(sys_get_temp_dir(), $filename);
    }

    /**
     * @param string $filename
     *
     * @throws ConvertStatusError
     * @throws FileNotFound
     * @throws DateTimeError
     *
     * @return ConvertStatus
     */
    public function getConvertStatus(string $filename): ConvertStatus
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ffmpeg' . $filename;

        if (!$this->file->exists($path)) {
            throw new FileNotFound('Konvertstatus nicht gefunden!');
        }

        $content = file_get_contents($path);

        if (empty($content)) {
            throw new ConvertStatusError();
        }

        preg_match_all('/frame=\s*(\d*)\s*fps=\s*(\d*)\s*q=\s*(\d*\.\d*)\s*size=\s*(\d*)kB\s*time=\s*(\d{2}\:\d{2}\:\d{2}\.\d{2})\s*bitrate=\s*(\d*\.\d*)/', $content, $hits);

        $count = count($hits[0]) - 1;

        if ($count == -1) {
            throw new ConvertStatusError();
        }

        return (new ConvertStatus())
            ->setFrame((int) $hits[1][$count])
            ->setFps((int) $hits[2][$count])
            ->setQuality((float) $hits[3][$count])
            ->setSize($hits[4][$count])
            ->setTime($this->dateTime->get($hits[5][$count]))
            ->setBitrate($hits[6][$count]);
    }

    /**
     * @param string $filename
     * @param string $frameNumber
     *
     * @throws DeleteError
     * @throws FileNotFound
     * @throws SetError
     * @throws GetError
     *
     * @return Image
     */
    public function getImageByFrame(string $filename, string $frameNumber): Image
    {
        $tmpFilename = 'tmpFrame' . rand() . '.png';
        $path = sys_get_temp_dir() . '/' . $tmpFilename;
        $this->execute(
            '-ss ' . $frameNumber . ' -i ' . escapeshellarg($filename) .
            ' -an -r 1 -vframes 1 -f image2 ' . $path . ' >/dev/null 2>/dev/null'
        );

        $image = ImageFactory::create();
        $image->load($path);
        $this->file->delete(sys_get_temp_dir(), $tmpFilename);

        return $image;
    }

    /**
     * @param string $parameters
     */
    private function execute(string $parameters)
    {
        $this->process->execute($this->ffpmegPath . ' ' . $parameters);
    }
}
