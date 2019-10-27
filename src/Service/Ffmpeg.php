<?php
namespace GibsonOS\Core\Service;

use DateTime;
use GibsonOS\Core\Dto\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Service\Ffmpeg\Media;
use GibsonOS\Core\Utility\File;

class Ffmpeg extends AbstractService
{
    /**
     * @var string
     */
    public $ffpmegPath;

    /**
     * Ffmpeg constructor.
     * @param string $ffpmegPath
     */
    public function __construct(string $ffpmegPath)
    {
        $this->ffpmegPath = $ffpmegPath;
    }

    /**
     * @param string $filename
     * @return string
     * @throws FileNotFound
     */
    public function getFileMetaDataString(string $filename): string
    {
        if (
            !file_exists($filename) ||
            !is_readable($filename)
        ) {
            throw new FileNotFound('Datei ' . $filename . ' existiert nicht!');
        }

        $ffMpeg = popen($this->ffpmegPath . ' -i ' . escapeshellarg($filename) . ' 2>&1', 'r');
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

        pclose($ffMpeg);

        return $output;
    }

    /**
     * @param Media $media
     * @param string $outputFilename
     * @param null|string $videoCodec
     * @param null|string $audioCodec
     * @param array $options
     * @throws DeleteError
     * @throws FileNotFound
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
                '-c:v ' . escapeshellarg($videoCodec) . ' ';
        }

        if (
            $videoCodec !== null &&
            $media->getSelectedVideoStreamId() !== null
        ) {
            $optionString .=
                '-map ' . $media->getSelectedVideoStreamId() . ' ' .
                '-c:a ' . escapeshellarg($audioCodec) . ' ';

            if ($media->getSelectedSubtitleStreamId() !== null) {
                $substitleStreamIds = array_keys($media->getSubtitleStreams());
                $optionString .=
                    '-vf subtitles=' . escapeshellarg($media->getFilename()) .
                    ':si=' . array_search($media->getSelectedSubtitleStreamId(), $substitleStreamIds) . ' ';
            }
        }

        foreach ($options as $key => $option) {
            $optionString .= '-' . $key . ' ' . escapeshellarg($option) . ' ';
        }

        $filename = 'ffmpeg' . File::getFilename($outputFilename);
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

        File::delete(sys_get_temp_dir(), $filename);
    }

    /**
     * @param string $filename
     * @return ConvertStatus
     * @throws ConvertStatusError
     * @throws FileNotFound
     */
    public function getConvertStatus(string $filename): ConvertStatus
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ffmpeg' . $filename;

        if (!file_exists($path)) {
            throw new FileNotFound('Konvertstatus nicht gefunden!');
        }

        $content = file_get_contents($path);
        preg_match_all('/frame=\s*(\d*)\s*fps=\s*(\d*)\s*q=\s*(\d*\.\d*)\s*size=\s*(\d*)kB\s*time=\s*(\d{2}\:\d{2}\:\d{2}\.\d{2})\s*bitrate=\s*(\d*\.\d*)/', $content, $hits);

        $count = count($hits[0]) - 1;

        if ($count == -1) {
            throw new ConvertStatusError();
        }
        
        return (new ConvertStatus())
            ->setFrame((int)$hits[1][$count])
            ->setFps((int)$hits[2][$count])
            ->setQuality((float)$hits[3][$count])
            ->setSize($hits[4][$count])
            ->setTime(new DateTime($hits[5][$count]))
            ->setBitrate($hits[6][$count]);
    }

    /**
     * @param string $filename
     * @param string $frameNumber
     * @return Image
     * @throws FileNotFound
     * @throws DeleteError
     */
    public function getImageByFrame(string $filename, string $frameNumber): Image
    {
        $tmpFilename = 'tmpFrame' . rand() . '.png';
        $path = sys_get_temp_dir() . '/' . $tmpFilename;
        $this->execute(
            '-ss ' . $frameNumber . ' -i ' . escapeshellarg($filename) .
            ' -an -r 1 -vframes 1 -f image2 ' . $path . ' >/dev/null 2>/dev/null'
        );

        $image = new Image();
        $image->load($path);
        File::delete(sys_get_temp_dir(), $tmpFilename);

        return $image;
    }

    /**
     * @param string $parameters
     */
    private function execute(string $parameters)
    {
        exec($this->ffpmegPath . ' ' . $parameters);
    }
}