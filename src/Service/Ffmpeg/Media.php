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
use GibsonOS\Core\Service\Ffmpeg;
use InvalidArgumentException;
use OutOfRangeException;

class Media
{
    /**
     * @var Ffmpeg
     */
    private $ffmpeg;

    /**
     * Video constructor.
     *
     * @param Ffmpeg $ffmpeg
     */
    public function __construct(Ffmpeg $ffmpeg)
    {
        $this->ffmpeg = $ffmpeg;
    }

    /**
     * @param string $filename
     *
     * @throws FileNotFound
     * @throws ProcessError
     *
     * @return MediaDto
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
     * @param MediaDto    $media
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
        MediaDto $media,
        string $outputFilename,
        string $videoCodec = null,
        string $audioCodec = null,
        array $options = []
    ) {
        $this->ffmpeg->convert($media, $outputFilename, $videoCodec, $audioCodec, $options);
    }

    /**
     * @param MediaDto $media
     * @param string   $outputFilename
     *
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws FileNotFound
     * @throws OpenError
     *
     * @return ConvertStatus
     */
    public function getConvertStatus(MediaDto $media, string $outputFilename): ConvertStatus
    {
        $convertStatus = $this->ffmpeg->getConvertStatus($outputFilename);
        $convertStatus->setFrames($media->getFrames());

        return $convertStatus;
    }

    /**
     * @param MediaDto $media
     * @param int      $second
     * @param int|null $frame
     *
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws LoadError
     * @throws NoVideoError
     *
     * @return Image
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
     * @param MediaDto $media
     * @param int      $frameNumber
     *
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws NoVideoError
     * @throws LoadError
     *
     * @return Image
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

    /**
     * @param MediaDto $media
     * @param array    $rawValues
     */
    private function calculateDuration(MediaDto $media, array $rawValues)
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

    /**
     * @param MediaDto    $media
     * @param array       $properties
     * @param string      $streamId
     * @param string|null $language
     * @param string      $options
     */
    private function addVideoStream(MediaDto $media, array $properties, string $streamId, ?string $language, string $options)
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

    /**
     * @param MediaDto    $media
     * @param array       $properties
     * @param string      $streamId
     * @param string|null $language
     * @param string      $options
     */
    private function addAudioStream(MediaDto $media, array $properties, string $streamId, ?string $language, string $options)
    {
        $stream = (new Audio())
            ->setLanguage($language)
            ->setFormat(isset($properties[0]) ? trim($properties[0]) : null)
            ->setFrequency(isset($properties[1]) ? trim($properties[1]) : null)
            ->setChannels(isset($properties[2]) ? trim($properties[2]) : null)
            ->setBitrate(isset($properties[4]) ? trim($properties[4]) : null);
        $media->setAudioStream($streamId, $stream);

        if (mb_stripos($options, '(default)')) {
            $media->selectAudioStream($streamId);
            $stream->setDefault(true);
        }
    }

    /**
     * @param MediaDto    $media
     * @param string      $streamId
     * @param string|null $language
     * @param string      $options
     */
    private function addSubtitleStream(MediaDto $media, string $streamId, ?string $language, string $options)
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

    /**
     * @param string $propertiesString
     *
     * @return array
     */
    private function getPropertiesFromString(string $propertiesString): array
    {
        $propertiesString = (string) preg_replace('/(\([^\(].),(.+?\))/', '$1{%%KOMMA%%}$2', $propertiesString);
        $properties = explode(',', $propertiesString);
        $properties[count($properties) - 1] = preg_replace('/\(.*?$/', '', $properties[count($properties) - 1]);

        $properties = array_map(
            function ($str) {
                return str_replace('{%%KOMMA%%}', ',', $str);
            },
            $properties
        );

        return $properties;
    }

    /**
     * @param string $string
     *
     * @return string|null
     */
    private function getLanguageFromString(string $string): ?string
    {
        $language = (string) preg_replace('/\(([a-zA-Z]{3})\)/', '$1', $string);

        if (mb_strlen($language) != 3) {
            return null;
        }

        return $language;
    }
}
