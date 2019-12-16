<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg;

use GibsonOS\Core\Dto\Ffmpeg\Stream\Audio;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Subtitle;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Video;
use InvalidArgumentException;
use JsonSerializable;

class Media implements JsonSerializable
{
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
     * @var string|null
     */
    private $selectedAudioStreamId;

    /**
     * @var string|null
     */
    private $selectedVideoStreamId;

    /**
     * @var string|null
     */
    private $selectedSubtitleStreamId;

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
     * Media constructor.
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): Media
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return Video[]
     */
    public function getVideoStreams(): array
    {
        return $this->videoStreams;
    }

    /**
     * @param Video[] $videoStreams
     */
    public function setVideoStreams(array $videoStreams): Media
    {
        $this->videoStreams = $videoStreams;

        return $this;
    }

    public function setVideoStream(string $id, Video $videoStream): Media
    {
        $this->videoStreams[$id] = $videoStream;

        return $this;
    }

    /**
     * @return Audio[]
     */
    public function getAudioStreams(): array
    {
        return $this->audioStreams;
    }

    /**
     * @param Audio[] $audioStreams
     */
    public function setAudioStreams(array $audioStreams): Media
    {
        $this->audioStreams = $audioStreams;

        return $this;
    }

    public function setAudioStream(string $id, Audio $audioStream): Media
    {
        $this->audioStreams[$id] = $audioStream;

        return $this;
    }

    /**
     * @return Subtitle[]
     */
    public function getSubtitleStreams(): array
    {
        return $this->subtitleStreams;
    }

    /**
     * @param Subtitle[] $subtitleStreams
     */
    public function setSubtitleStreams(array $subtitleStreams): Media
    {
        $this->subtitleStreams = $subtitleStreams;

        return $this;
    }

    public function setSubtitleStream(string $id, Subtitle $subtitleStream): Media
    {
        $this->subtitleStreams[$id] = $subtitleStream;

        return $this;
    }

    public function getSelectedAudioStreamId(): ?string
    {
        return $this->selectedAudioStreamId;
    }

    public function getSelectedVideoStreamId(): ?string
    {
        return $this->selectedVideoStreamId;
    }

    public function getSelectedSubtitleStreamId(): ?string
    {
        return $this->selectedSubtitleStreamId;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): Media
    {
        $this->duration = $duration;

        return $this;
    }

    public function getFrames(): int
    {
        return $this->frames;
    }

    public function setFrames(int $frames): Media
    {
        $this->frames = $frames;

        return $this;
    }

    public function getBitRate(): int
    {
        return $this->bitRate;
    }

    public function setBitRate(int $bitRate): Media
    {
        $this->bitRate = $bitRate;

        return $this;
    }

    public function getSelectedAudioStream(): Audio
    {
        return $this->audioStreams[$this->selectedAudioStreamId];
    }

    public function getSelectedVideoStream(): Video
    {
        return $this->videoStreams[$this->selectedVideoStreamId];
    }

    public function getSelectedSubtitleStream(): Subtitle
    {
        return $this->subtitleStreams[$this->selectedSubtitleStreamId];
    }

    public function hasAudio(): bool
    {
        return (bool) count($this->getAudioStreams());
    }

    public function hasVideo(): bool
    {
        return (bool) count($this->getVideoStreams());
    }

    public function hasSubtitle(): bool
    {
        return (bool) count($this->getSubtitleStreams());
    }

    public function selectAudioStream(string $streamId)
    {
        if (!isset($this->audioStreams[$streamId])) {
            throw new InvalidArgumentException('Audio Stream (' . $streamId . ') existiert nicht!');
        }

        $this->selectedAudioStreamId = $streamId;
    }

    public function selectSubtitleStream(string $streamId)
    {
        if (!isset($this->subtitleStreams[$streamId])) {
            throw new InvalidArgumentException('Subtitle Stream (' . $streamId . ') existiert nicht!');
        }

        $this->selectedSubtitleStreamId = $streamId;
    }

    public function selectVideoStream(string $streamId)
    {
        if (!isset($this->videoStreams[$streamId])) {
            throw new InvalidArgumentException('Video Stream (' . $streamId . ') existiert nciht!');
        }

        $this->selectedVideoStreamId = $streamId;
    }

    public function jsonSerialize(): array
    {
        return [
            'filename' => $this->getFilename(),
            'videoStreams' => $this->getVideoStreams(),
            'audioStreams' => $this->getAudioStreams(),
            'subtitleStreams' => $this->getSubtitleStreams(),
            'selectedAudioStreamId' => $this->getSelectedAudioStreamId(),
            'selectedVideoStreamId' => $this->getSelectedVideoStreamId(),
            'selectedSubtitleStreamId' => $this->getSelectedSubtitleStreamId(),
            'duration' => $this->getDuration(),
            'frames' => $this->getFrames(),
            'bitRate' => $this->getBitRate(),
        ];
    }
}
