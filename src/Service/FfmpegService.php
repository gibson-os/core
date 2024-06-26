<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Dto\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Dto\Ffmpeg\Media;
use GibsonOS\Core\Dto\Image as ImageDto;
use GibsonOS\Core\Enum\Ffmpeg\ConvertStatus as ConvertStatusEnum;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertException;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\FfmpegException;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Exception\ProcessError;

class FfmpegService
{
    /**
     * @throws GetError
     */
    public function __construct(
        #[GetEnv('FFMPEG_PATH')]
        private readonly string $ffmpegPath,
        #[GetEnv('FFPROBE_PATH')]
        private readonly string $ffprobePath,
        private readonly DateTimeService $dateTimeService,
        private readonly FileService $fileService,
        private readonly ProcessService $processService,
        private readonly ImageService $imageService,
    ) {
    }

    /**
     * @throws FileNotFound
     * @throws ProcessError
     */
    public function getFileMetaDataString(string $filename): string
    {
        if (
            !$this->fileService->exists($filename)
            || !$this->fileService->isReadable($filename)
        ) {
            throw new FileNotFound(sprintf('Datei %s existiert nicht!', $filename));
        }

        $ffMpeg = $this->processService->open(sprintf('%s -i %s', $this->ffmpegPath, escapeshellarg($filename)), 'r');
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

        $this->processService->close($ffMpeg);

        return $output;
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ConvertException
     */
    public function convert(
        Media $media,
        string $outputFilename,
        ?string $videoCodec = null,
        ?string $audioCodec = null,
        array $options = [],
    ): void {
        $optionString = $this->getOption($options, 'activation_bytes', '');
        $optionString .= '-i ' . escapeshellarg($media->getFilename()) . ' ';

        if (
            $audioCodec !== null
            && $media->getSelectedAudioStreamId() !== null
        ) {
            $optionString .=
                '-map ' . $media->getSelectedAudioStreamId() . ' ' .
                '-c:a ' . escapeshellarg($audioCodec) . ' '
            ;
            $optionString = $this->getOption($options, 'ac', $optionString);
            $optionString = $this->getOption($options, 'vol', $optionString);
        }

        if (
            $videoCodec !== null
            && $media->getSelectedVideoStreamId() !== null
        ) {
            $optionString .=
                '-map ' . $media->getSelectedVideoStreamId() . ' ' .
                '-c:v ' . escapeshellarg($videoCodec) . ' ';

            if ($media->getSelectedSubtitleStreamId() === null) {
                $optionString .= '-sn ';
            } else {
                $subtitleStreamIds = array_keys($media->getSubtitleStreams());
                $optionString .=
                    '-vf subtitles=\'' . preg_replace('/([\[\]])/', '\\\\$1', $media->getFilename()) . '\'' .
                    ':si=' . (array_search($media->getSelectedSubtitleStreamId(), $subtitleStreamIds) ?: 0) . ' ';
            }
        }

        foreach ($options as $key => $option) {
            $optionString .= '-' . $key . ' ' . escapeshellarg((string) $option) . ' ';
        }

        $outputFileNameWithoutPath = $this->fileService->getFilename($outputFilename);
        $filename = 'ffmpeg' . $outputFileNameWithoutPath;
        $logPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        $this->execute(sprintf(
            '%s%s > %s 2> %s',
            $optionString,
            escapeshellarg($outputFilename),
            escapeshellarg($logPath),
            escapeshellarg($logPath),
        ));

        $this->fileService->delete(sys_get_temp_dir(), $filename);

        if (!$this->fileService->exists($outputFilename)) {
            throw new ConvertException(
                sprintf('Konvertieren war nicht erfolgreich! Datei %s existiert nicht!', $outputFilename),
            );
        }

        $filesize = $this->fileService->size($outputFilename);

        if ($filesize === 0) {
            $this->fileService->delete($this->fileService->getDir($outputFilename), $outputFileNameWithoutPath);

            throw new ConvertException(
                sprintf('Konvertieren war nicht erfolgreich! Datei %s hat keine Größe!', $outputFilename),
            );
        }
    }

    /**
     * @throws ConvertStatusError
     * @throws FileNotFound
     * @throws OpenError
     */
    public function getConvertStatus(string $filename): ConvertStatus
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ffmpeg' . $filename;

        if (!$this->fileService->exists($path)) {
            throw new FileNotFound(sprintf('Konvertstatus "%s" existiert nicht! Maybe PrivateTmp? /usr/lib/systemd/system/apache2.service', $path));
        }

        $content = $this->fileService->readLastLine($path);

        if (!preg_match(
            '/frame=\s*(\d*)\s*' .
            'fps=\s*(\d*\.?\d*)\s*' .
            'q=\s*(\d*\.\d*)\s*' .
            'size=\s*(\d*)kB\s*' .
            'time=\s*(\d{2}:\d{2}:\d{2}\.\d{2})\s*' .
            'bitrate=\s*(\d*\.\d*)/',
            $content,
            $hits,
        )) {
            throw new ConvertStatusError();
        }

        return (new ConvertStatus(ConvertStatusEnum::GENERATE))
            ->setFrame((int) $hits[1])
            ->setFps((int) round((float) $hits[2]))
            ->setQuality((float) $hits[3])
            ->setSize((int) $hits[4])
            ->setTime($this->dateTimeService->get($hits[5]))
            ->setBitrate((float) $hits[6])
        ;
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws LoadError
     */
    public function getImageByFrame(string $filename, string $frameNumber): ImageDto
    {
        $tmpFilename = 'tmpFrame' . rand() . '.png';
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpFilename;
        $this->execute(sprintf(
            '-ss %s -i %s -an -r 1 -vframes 1 -f image2 %s >/dev/null 2>/dev/null',
            $frameNumber,
            escapeshellarg($filename),
            $path,
        ));

        $image = $this->imageService->load($path);
        $this->fileService->delete(sys_get_temp_dir(), $tmpFilename);

        return $image;
    }

    /**
     * @throws FfmpegException
     */
    public function getChecksum(string $filename): string
    {
        if (
            !$this->fileService->exists($filename)
            || !$this->fileService->isReadable($filename)
        ) {
            throw new FfmpegException(sprintf('File %s not found!', $filename));
        }

        try {
            $ffMpeg = $this->processService->open(sprintf('%s %s', $this->ffprobePath, escapeshellarg($filename)), 'r');
        } catch (ProcessError) {
            throw new FfmpegException('Ffprobe not found!');
        }

        while ($out = fgets($ffMpeg)) {
            $matches = ['', ''];

            if (preg_match('/file checksum == (\w*)/', $out, $matches) !== 1) {
                continue;
            }

            $this->processService->close($ffMpeg);

            return $matches[1];
        }

        $this->processService->close($ffMpeg);

        throw new FfmpegException('Checksum not found!');
    }

    private function execute(string $parameters): void
    {
        $this->processService->execute($this->ffmpegPath . ' ' . $parameters);
    }

    private function getOption(array &$options, string $key, string $optionString): string
    {
        if (!isset($options[$key])) {
            return $optionString;
        }

        $optionString .= '-' . $key . ' ' . escapeshellarg((string) $options[$key]) . ' ';
        unset($options[$key]);

        return $optionString;
    }
}
