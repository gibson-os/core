<?php
namespace GibsonOS\Core\Service\Ffmpeg;

use DateInterval;
use DateTime;
use Exception;
use GibsonOS\Core\Dto\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Audio;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Subtitle;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Video;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\Ffmpeg\NoVideoError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Service\Ffmpeg;
use GibsonOS\Core\Service\Image as ImageService;
use InvalidArgumentException;
use OutOfRangeException;

class Media
{
    /**
     * @var Ffmpeg
     */
    private $ffmpeg;
    /**
     * @var string
     */
    private $filename;
    /**
     * @var Video[]
     */
    private $videoStreams = [];
    /**
     * @var Audio[]
     */
    private $audioStreams = [];
    /**
     * @var Subtitle[]
     */
    private $subtitleStreams = [];
    /**
     * @var null|string
     */
    private $selectedAudioStreamId = null;
    /**
     * @var null|string
     */
    private $selectedVideoStreamId = null;
    /**
     * @var null|string
     */
    private $selectedSubtitleStreamId = null;
    /**
     * @var float
     */
    private $duration = 0;
    /**
     * @var int
     */
    private $frames = 0;
    /**
     * @var int
     */
    private $bitRate = 0;

    /**
     * Video constructor.
     * @param Ffmpeg $ffmpeg
     * @param string $filename
     * @throws FileNotFound
     */
    public function __construct(Ffmpeg $ffmpeg, string $filename)
    {
        $this->ffmpeg = $ffmpeg;
        $this->filename = $filename;

        $this->load();
    }

    /**
     * @throws FileNotFound
     */
    private function load()
    {
        $loadString = $this->ffmpeg->getFileMetaDataString($this->filename);

        if (!preg_match('/Duration:\s*(\d{2}):(\d{2}):(\d{2}).(\d{2}),.*bitrate:\s*(.*)\s*kb/', $loadString, $hits)) {
            throw new InvalidArgumentException('Keine Video Metadaten gefunden!');
        }

        $this->calculateDuration($hits);
        $this->bitRate = (int)trim($hits[5]);

        if (!preg_match_all('/Stream[^#]#(\d+:\d+)([^:]*):([^:]*):(.*)/', $loadString, $hits)) {
            return;
        }

        for ($i = 0; $i < count($hits[0]); $i++) {
            $options = preg_replace('/(\([^\)]*),/', '$1', $hits[4][$i]);
            $options = preg_replace('/(\[[^\]]*),/', '$1', $options);
            $properties = $this->getPropertiesFromString($hits[4][$i]);
            $language = $this->getLanguageFromString($hits[2][$i]);

            switch (strtolower(trim($hits[3][$i]))) {
                case 'video':
                    $this->addVideoStream($properties, $hits[1][$i], $language, $options);
                    break;
                case 'audio':
                    $this->addAudioStream($properties, $hits[1][$i], $language, $options);
                    break;
                case 'subtitle':
                    $this->addSubtitleStream($hits[1][$i], $language, $options);
                    break;
            }
        }

        if ($this->hasVideo()) {
            if (is_null($this->selectedVideoStreamId)) {
                $streamIds = array_keys($this->videoStreams);
                $this->selectVideoStream($streamIds[0]);
            }

            $this->frames = round($this->duration * $this->getSelectedVideoStream()->getFps());
        }

        if (
            is_null($this->selectedAudioStreamId) &&
            $this->hasAudio()
        ) {
            $streamIds = array_keys($this->audioStreams);
            $this->selectAudioStream($streamIds[0]);
        }
    }

    /**
     * @param string $outputFilename
     * @param null|string $videoCodec
     * @param null|string $audioCodec
     * @param array $options
     * @throws DeleteError
     * @throws FileNotFound
     */
    public function convert(
        string $outputFilename,
        string $videoCodec = null,
        string $audioCodec = null,
        array $options = []
    ) {
        $this->ffmpeg->convert($this, $outputFilename, $videoCodec, $audioCodec, $options);
    }

    /**
     * @param string $outputFilename
     * @return ConvertStatus
     * @throws FileNotFound
     * @throws ConvertStatusError
     */
    public function getConvertStatus(string $outputFilename): ConvertStatus
    {
        $convertStatus = $this->ffmpeg->getConvertStatus($outputFilename);
        $convertStatus->setFrames($this->getFrames());

        return $convertStatus;
    }

    /**
     * @param int $second
     * @param null|int $frame
     * @return ImageService
     * @throws DeleteError
     * @throws FileNotFound
     * @throws NoVideoError
     */
    public function getImageBySecond(int $second, int $frame = null): ImageService
    {
        if ($second > $this->getDuration()) {
            throw new OutOfRangeException(
                'Sekunde ' . $second . ' ist größer als ' . $this->getDuration() . '!'
            );
        }

        return $this->getImageByFrame((int)(($second * $this->getSelectedVideoStream()->getFps()) + $frame));
    }

    /**
     * @param int $frameNumber
     * @return ImageService
     * @throws DeleteError
     * @throws FileNotFound
     * @throws NoVideoError
     */
    public function getImageByFrame(int $frameNumber): ImageService
    {
        if (!$this->hasVideo()) {
            throw new NoVideoError();
        }

        if ($frameNumber > $this->getFrames()) {
            throw new OutOfRangeException(
                'Frame Nummer ' . $frameNumber . ' ist größer als ' . $this->getFrames() . '!'
            );
        }

        try {
            $date = (new DateTime('01.01.2000 00:00:00'))->add(
                new DateInterval('PT' . (int)($frameNumber / $this->getSelectedVideoStream()->getFps()) . 'S')
            );
        } catch (Exception $e) {
            throw new OutOfRangeException('Frame Nummer ' . $frameNumber . ' kann nicht addiert werden!');
        }

        return $this->ffmpeg->getImageByFrame(
            $this->getFilename(),
            $date->format('H:i:s') . '.' . $frameNumber % $this->getSelectedVideoStream()->getFps()
        );
    }

    /**
     * @param string $streamId
     */
    public function selectAudioStream(string $streamId)
    {
        if (!isset($this->audioStreams[$streamId])) {
            throw new InvalidArgumentException('Audio Stream (' . $streamId . ') existiert nicht!');
        }

        $this->selectedAudioStreamId = $streamId;
    }

    /**
     * @param string $streamId
     */
    public function selectSubtitleStream(string $streamId)
    {
        if (!isset($this->subtitleStreams[$streamId])) {
            throw new InvalidArgumentException('Subtitle Stream (' . $streamId . ') existiert nicht!');
        }

        $this->selectedSubtitleStreamId = $streamId;
    }

    /**
     * @param string $streamId
     */
    public function selectVideoStream(string $streamId)
    {
        if (!isset($this->videoStreams[$streamId])) {
            throw new InvalidArgumentException('Video Stream (' . $streamId . ') existiert nciht!');
        }

        $this->selectedVideoStreamId = $streamId;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return Video[]
     */
    public function getVideoStreams(): array
    {
        return $this->videoStreams;
    }

    /**
     * @return Audio[]
     */
    public function getAudioStreams(): array
    {
        return $this->audioStreams;
    }

    /**
     * @return Subtitle[]
     */
    public function getSubtitleStreams(): array
    {
        return $this->subtitleStreams;
    }

    /**
     * @return null|string
     */
    public function getSelectedAudioStreamId(): ?string
    {
        return $this->selectedAudioStreamId;
    }

    /**
     * @return null|string
     */
    public function getSelectedVideoStreamId(): ?string
    {
        return $this->selectedVideoStreamId;
    }

    /**
     * @return null|string
     */
    public function getSelectedSubtitleStreamId(): ?string
    {
        return $this->selectedSubtitleStreamId;
    }

    /**
     * @return Audio|null
     */
    public function getSelectedAudioStream(): ?Audio
    {
        return $this->audioStreams[$this->selectedAudioStreamId];
    }

    /**
     * @return Video|null
     */
    public function getSelectedVideoStream(): ?Video
    {
        return $this->videoStreams[$this->selectedVideoStreamId];
    }

    /**
     * @return Subtitle|null
     */
    public function getSelectedSubtitleStream(): ?Subtitle
    {
        return $this->subtitleStreams[$this->selectedSubtitleStreamId];
    }

    /**
     * @return bool
     */
    public function hasAudio(): bool
    {
        return (bool)count($this->getAudioStreams());
    }

    /**
     * @return bool
     */
    public function hasVideo(): bool
    {
        return (bool)count($this->getVideoStreams());
    }

    /**
     * @return bool
     */
    public function hasSubtitle(): bool
    {
        return (bool)count($this->getSubtitleStreams());
    }

    /**
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @return int
     */
    public function getFrames(): int
    {
        return $this->frames;
    }

    /**
     * @return int
     */
    public function getBitRate(): int
    {
        return $this->bitRate;
    }

    /**
     * @param array $rawValues
     */
    private function calculateDuration(array $rawValues)
    {
        $duration = 0;
        $durationMultiplier = [
            1 => 3600,
            2 => 60,
            3 => 1,
            4 => .01
        ];

        for ($i = 1; $i < count($rawValues)-1; $i++) {
            $duration += $rawValues[$i] * $durationMultiplier[$i];
        }

        $this->duration = $duration;
    }

    /**
     * @param array $properties
     * @param string $streamId
     * @param string|null $language
     * @param string $options
     */
    private function addVideoStream(array $properties, string $streamId, ?string $language, string $options)
    {
        $stream = (new Video())
            ->setLanguage($language)
            ->setCodec(isset($properties[0]) ? trim($properties[0]) : null)
            ->setColorSpace(isset($properties[1]) ? trim($properties[1]) : null);

        if (preg_match('/(,|^)\s*(\d+(\.\d+)?)\s*fps(,|$)/', $options, $fps)) {
            $stream->setFps((int)$fps[2]);
        } else if (preg_match('/(,|^)\s*(\d+(\.\d+)?)\s*tbr(,|$)/', $options, $fps)) {
            $stream->setFps((int)$fps[2]);
        }

        if (preg_match('/(\d+)x(\d+)/', $properties[2], $size)) {
            $stream->setWidth((int)$size[1]);
            $stream->setHeight((int)$size[2]);
        }

        $this->videoStreams[$streamId] = $stream;

        if (mb_stripos($options, '(default)')) {
            $this->selectVideoStream($streamId);
            $stream->setDefault(true);
        }
    }

    /**
     * @param array $properties
     * @param string $streamId
     * @param string|null $language
     * @param string $options
     */
    private function addAudioStream(array $properties, string $streamId, ?string $language, string $options)
    {
        $stream = (new Audio())
            ->setLanguage($language)
            ->setFormat(isset($properties[0]) ? trim($properties[0]) : null)
            ->setFrequency(isset($properties[1]) ? trim($properties[1]) : null)
            ->setChannels(isset($properties[2]) ? trim($properties[2]) : null)
            ->setBitrate(isset($properties[4]) ? trim($properties[4]) : null);
        $this->audioStreams[$streamId] = $stream;

        if (mb_stripos($options, '(default)')) {
            $this->selectAudioStream($streamId);
            $stream->setDefault(true);
        }
    }

    /**
     * @param string $streamId
     * @param string|null $language
     * @param string $options
     */
    private function addSubtitleStream(string $streamId, ?string $language, string $options)
    {
        $stream = (new Subtitle())->setLanguage($language);
        $this->subtitleStreams[$streamId] = $stream;

        if (mb_stripos($options, '(forced)')) {
            $this->selectSubtitleStream($streamId);
            $stream->setForced(true);
        }

        if (mb_stripos($options, '(default)')) {
            $this->selectSubtitleStream($streamId);
            $stream->setDefault(true);
        }
    }

    /**
     * @param string $propertiesString
     * @return array
     */
    private function getPropertiesFromString(string $propertiesString): array
    {
        $propertiesString = preg_replace('/(\([^\(].),(.+?\))/', '$1{%%KOMMA%%}$2', $propertiesString);
        $properties = explode(',', $propertiesString);
        $properties[count($properties)-1] = preg_replace('/\(.*?$/', '', $properties[count($properties)-1]);

        $properties = array_map(
            function($str) {
                return str_replace('{%%KOMMA%%}', ',', $str);
            },
            $properties
        );

        return $properties;
    }

    /**
     * @param string $string
     * @return null|string
     */
    private function getLanguageFromString(string $string): ?string
    {
        $language = preg_replace('/\(([a-zA-Z]{3})\)/', '$1', $string);

        if (mb_strlen($language) != 3) {
            return null;
        }

        return $language;
    }
}