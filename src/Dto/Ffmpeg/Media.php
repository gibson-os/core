<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg;

use GibsonOS\Core\Dto\Ffmpeg\Stream\Audio;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Subtitle;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Video;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\Ffmpeg\NoSubtitleError;

class Media implements \JsonSerializable
{
    /**
     * @var Video[]
     */
    private array $videoStreams = [];

    /**
     * @var Audio[]
     */
    private array $audioStreams = [];

    /**
     * @var Subtitle[]
     */
    private array $subtitleStreams = [];

    private ?string $selectedAudioStreamId = null;

    private ?string $selectedVideoStreamId = null;

    private ?string $selectedSubtitleStreamId = null;

    private float $duration = 0.0;

    private int $frames = 0;

    private int $bitRate = 0;

    public function __construct(private string $filename)
    {
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

    /**
     * @throws NoAudioError
     */
    public function getSelectedAudioStream(): Audio
    {
        if (!isset($this->audioStreams[$this->selectedAudioStreamId])) {
            throw new NoAudioError('Selektierter Audio Stream existiert nicht!');
        }

        return $this->audioStreams[$this->selectedAudioStreamId];
    }

    /**
     * @throws NoAudioError
     */
    public function getSelectedVideoStream(): Video
    {
        if (!isset($this->videoStreams[$this->selectedVideoStreamId])) {
            throw new NoAudioError('Selektierter Video Stream existiert nicht!');
        }

        return $this->videoStreams[$this->selectedVideoStreamId];
    }

    /**
     * @throws NoSubtitleError
     */
    public function getSelectedSubtitleStream(): Subtitle
    {
        if (!isset($this->subtitleStreams[$this->selectedSubtitleStreamId])) {
            throw new NoSubtitleError('Selektierter Untertitel Stream existiert nicht!');
        }

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

    public function selectAudioStream(?string $streamId): Media
    {
        if ($streamId !== null && !isset($this->audioStreams[$streamId])) {
            throw new \InvalidArgumentException('Audio Stream (' . $streamId . ') existiert nicht!');
        }

        $this->selectedAudioStreamId = $streamId;

        return $this;
    }

    public function selectSubtitleStream(?string $streamId): Media
    {
        if ($streamId !== null && !isset($this->subtitleStreams[$streamId])) {
            throw new \InvalidArgumentException('Subtitle Stream (' . $streamId . ') existiert nicht!');
        }

        $this->selectedSubtitleStreamId = $streamId;

        return $this;
    }

    public function selectVideoStream(?string $streamId): Media
    {
        if ($streamId !== null && !isset($this->videoStreams[$streamId])) {
            throw new \InvalidArgumentException('Video Stream (' . $streamId . ') existiert nicht!');
        }

        $this->selectedVideoStreamId = $streamId;

        return $this;
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
