<?php
/** @noinspection PhpUnused */
declare(strict_types=1);

namespace GibsonOS\Core\Service\Ffmpeg;

use DateInterval;
use DateTime;
use Exception;
use GibsonOS\Core\Dto\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Dto\Ffmpeg\Media as MediaDto;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Audio;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Subtitle;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Video;
use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\Ffmpeg\NoVideoError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Service\FfmpegService;
use InvalidArgumentException;
use OutOfRangeException;

class MediaService
{
    /**
     * @var FfmpegService
     */
    private $ffmpeg;

    /**
     * Video constructor.
     */
    public function __construct(FfmpegService $ffmpeg)
    {
        $this->ffmpeg = $ffmpeg;
    }

    /**
     * @throws FileNotFound
     * @throws ProcessError
     */
    public function getMedia(string $filename): MediaDto
    {
        $media = new MediaDto($filename);
        $loadString = $this->ffmpeg->getFileMetaDataString($media->getFilename());

        if (!preg_match('/Duration:\s*(\d{2}):(\d{2}):(\d{2}).(\d{2}),.*bitrate:\s*(.*)\s*kb/', $loadString, $hits)) {
            throw new InvalidArgumentException('Keine Video Metadaten gefunden!');
        }

        $this->calculateDuration($media, $hits);
        $media->setBitRate((int) trim($hits[5]));

        if (!preg_match_all('/Stream[^#]#(\d+:\d+)([^:]*):([^:]*):(.*)/', $loadString, $hits)) {
            return $media;
        }

        for ($i = 0; $i < count($hits[0]); ++$i) {
            $options = (string) preg_replace('/(\([^\)]*),/', '$1', $hits[4][$i]);
            $options = (string) preg_replace('/(\[[^\]]*),/', '$1', $options);
            $properties = $this->getPropertiesFromString($hits[4][$i]);
            $language = $this->getLanguageFromString($hits[2][$i]);

            switch (strtolower(trim($hits[3][$i]))) {
                case 'video':
                    $this->addVideoStream($media, $properties, $hits[1][$i], $language, $options);

                    break;
                case 'audio':
                    $this->addAudioStream($media, $properties, $hits[1][$i], $language, $options);

                    break;
                case 'subtitle':
                    $this->addSubtitleStream($media, $hits[1][$i], $language, $options);

                    break;
            }
        }

        if ($media->hasVideo()) {
            if ($media->getSelectedVideoStreamId() === null) {
                $streamIds = array_keys($media->getVideoStreams());
                $media->selectVideoStream($streamIds[0]);
            }

            $media->setFrames((int) ceil($media->getDuration() * $media->getSelectedVideoStream()->getFps()));
        }

        if (
            $media->getSelectedAudioStreamId() === null &&
            $media->hasAudio()
        ) {
            $streamIds = array_keys($media->getAudioStreams());
            $media->selectAudioStream($streamIds[0]);
        }

        return $media;
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     */
    public function convert(
        MediaDto $media,
        string $outputFilename,
        string $videoCodec = null,
        string $audioCodec = null,
        array $options = []
    ): void {
        $this->ffmpeg->convert($media, $outputFilename, $videoCodec, $audioCodec, $options);
    }

    /**
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws FileNotFound
     * @throws OpenError
     * @throws SetError
     */
    public function getConvertStatus(MediaDto $media, string $outputFilename): ConvertStatus
    {
        $convertStatus = $this->ffmpeg->getConvertStatus($outputFilename);
        $convertStatus->setFrames($media->getFrames());

        return $convertStatus;
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws LoadError
     * @throws NoVideoError
     */
    public function getImageBySecond(MediaDto $media, int $second, int $frame = null): Image
    {
        if ($second > $media->getDuration()) {
            throw new OutOfRangeException(
                'Sekunde ' . $second . ' ist größer als ' . $media->getDuration() . '!'
            );
        }

        return $this->getImageByFrame($media, (int) (($second * $media->getSelectedVideoStream()->getFps()) + $frame));
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws NoVideoError
     * @throws LoadError
     */
    public function getImageByFrame(MediaDto $media, int $frameNumber): Image
    {
        if (!$media->hasVideo()) {
            throw new NoVideoError();
        }

        if ($frameNumber > $media->getFrames()) {
            throw new OutOfRangeException(
                'Frame Nummer ' . $frameNumber . ' ist größer als ' . $media->getFrames() . '!'
            );
        }

        try {
            $date = (new DateTime('01.01.2000 00:00:00'))->add(
                new DateInterval('PT' . (int) ($frameNumber / $media->getSelectedVideoStream()->getFps()) . 'S')
            );
        } catch (Exception $e) {
            throw new OutOfRangeException('Frame Nummer ' . $frameNumber . ' kann nicht addiert werden!');
        }

        return $this->ffmpeg->getImageByFrame(
            $media->getFilename(),
            $date->format('H:i:s') . '.' . $frameNumber % $media->getSelectedVideoStream()->getFps()
        );
    }

    private function calculateDuration(MediaDto $media, array $rawValues): void
    {
        $duration = 0;
        $durationMultiplier = [
            1 => 3600,
            2 => 60,
            3 => 1,
            4 => .01,
        ];

        for ($i = 1; $i < count($rawValues) - 1; ++$i) {
            $duration += $rawValues[$i] * $durationMultiplier[$i];
        }

        $media->setDuration($duration);
    }

    private function addVideoStream(MediaDto $media, array $properties, string $streamId, ?string $language, string $options): void
    {
        $stream = (new Video())
            ->setLanguage($language)
            ->setCodec(isset($properties[0]) ? trim($properties[0]) : null)
            ->setColorSpace(isset($properties[1]) ? trim($properties[1]) : null);

        if (preg_match('/(,|^)\s*(\d+(\.\d+)?)\s*fps(,|$)/', $options, $fps)) {
            $stream->setFps((int) $fps[2]);
        } elseif (preg_match('/(,|^)\s*(\d+(\.\d+)?)\s*tbr(,|$)/', $options, $fps)) {
            $stream->setFps((int) $fps[2]);
        }

        if (preg_match('/(\d+)x(\d+)/', $properties[2], $size)) {
            $stream->setWidth((int) $size[1]);
            $stream->setHeight((int) $size[2]);
        }

        $media->setVideoStream($streamId, $stream);

        if (mb_stripos($options, '(default)')) {
            $media->selectVideoStream($streamId);
            $stream->setDefault(true);
        }
    }

    private function addAudioStream(MediaDto $media, array $properties, string $streamId, ?string $language, string $options): void
    {
        $stream = (new Audio())
            ->setLanguage($language)
            ->setFormat(isset($properties[0]) ? trim($properties[0]) : null)
            ->setFrequency(isset($properties[1]) ? trim($properties[1]) : null)
            ->setChannels(isset($properties[2]) ? trim($properties[2]) : null)
            ->setBitRate(isset($properties[4]) ? trim($properties[4]) : null);
        $media->setAudioStream($streamId, $stream);

        if (mb_stripos($options, '(default)')) {
            $media->selectAudioStream($streamId);
            $stream->setDefault(true);
        }
    }

    private function addSubtitleStream(MediaDto $media, string $streamId, ?string $language, string $options): void
    {
        $stream = (new Subtitle())->setLanguage($language);
        $media->setSubtitleStream($streamId, $stream);

        if (mb_stripos($options, '(forced)')) {
            $media->selectSubtitleStream($streamId);
            $stream->setForced(true);
        }

        if (mb_stripos($options, '(default)')) {
            $media->selectSubtitleStream($streamId);
            $stream->setDefault(true);
        }
    }

    private function getPropertiesFromString(string $propertiesString): array
    {
        $propertiesString = (string) preg_replace('/(\([^\(].),(.+?\))/', '$1{%%KOMMA%%}$2', $propertiesString);
        $properties = explode(',', $propertiesString);
        $properties[count($properties) - 1] = preg_replace('/\(.*?$/', '', $properties[count($properties) - 1]);

        $properties = array_map(
            function (string $str) {
                return str_replace('{%%KOMMA%%}', ',', $str);
            },
            $properties
        );

        return $properties;
    }

    private function getLanguageFromString(string $string): ?string
    {
        $language = (string) preg_replace('/\(([a-zA-Z]{3})\)/', '$1', $string);

        if (mb_strlen($language) != 3) {
            return null;
        }

        return $language;
    }
}
