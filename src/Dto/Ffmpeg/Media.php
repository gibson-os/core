<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg;

use GibsonOS\Core\Dto\Ffmpeg\Stream\Audio;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Subtitle;
use GibsonOS\Core\Dto\Ffmpeg\Stream\Video;
use InvalidArgumentException;

class Media
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
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     *
     * @return Media
     */
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
     *
     * @return Media
     */
    public function setVideoStreams(array $videoStreams): Media
    {
        $this->videoStreams = $videoStreams;

        return $this;
    }

    /**
     * @param string $id
     * @param Video  $videoStream
     *
     * @return Media
     */
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
     *
     * @return Media
     */
    public function setAudioStreams(array $audioStreams): Media
    {
        $this->audioStreams = $audioStreams;

        return $this;
    }

    /**
     * @param string $id
     * @param Audio  $audioStream
     *
     * @return Media
     */
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
     *
     * @return Media
     */
    public function setSubtitleStreams(array $subtitleStreams): Media
    {
        $this->subtitleStreams = $subtitleStreams;

        return $this;
    }

    /**
     * @param string   $id
     * @param Subtitle $subtitleStream
     *
     * @return Media
     */
    public function setSubtitleStream(string $id, Subtitle $subtitleStream): Media
    {
        $this->subtitleStreams[$id] = $subtitleStream;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSelectedAudioStreamId(): ?string
    {
        return $this->selectedAudioStreamId;
    }

    /**
     * @param string|null $selectedAudioStreamId
     *
     * @return Media
     */
    public function setSelectedAudioStreamId(?string $selectedAudioStreamId): Media
    {
        $this->selectedAudioStreamId = $selectedAudioStreamId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSelectedVideoStreamId(): ?string
    {
        return $this->selectedVideoStreamId;
    }

    /**
     * @param string|null $selectedVideoStreamId
     *
     * @return Media
     */
    public function setSelectedVideoStreamId(?string $selectedVideoStreamId): Media
    {
        $this->selectedVideoStreamId = $selectedVideoStreamId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSelectedSubtitleStreamId(): ?string
    {
        return $this->selectedSubtitleStreamId;
    }

    /**
     * @param string|null $selectedSubtitleStreamId
     *
     * @return Media
     */
    public function setSelectedSubtitleStreamId(?string $selectedSubtitleStreamId): Media
    {
        $this->selectedSubtitleStreamId = $selectedSubtitleStreamId;

        return $this;
    }

    /**
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @param float $duration
     *
     * @return Media
     */
    public function setDuration(float $duration): Media
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrames(): int
    {
        return $this->frames;
    }

    /**
     * @param int $frames
     *
     * @return Media
     */
    public function setFrames(int $frames): Media
    {
        $this->frames = $frames;

        return $this;
    }

    /**
     * @return int
     */
    public function getBitRate(): int
    {
        return $this->bitRate;
    }

    /**
     * @param int $bitRate
     *
     * @return Media
     */
    public function setBitRate(int $bitRate): Media
    {
        $this->bitRate = $bitRate;

        return $this;
    }

    /**
     * @return Audio
     */
    public function getSelectedAudioStream(): Audio
    {
        return $this->audioStreams[$this->selectedAudioStreamId];
    }

    /**
     * @return Video
     */
    public function getSelectedVideoStream(): Video
    {
        return $this->videoStreams[$this->selectedVideoStreamId];
    }

    /**
     * @return Subtitle
     */
    public function getSelectedSubtitleStream(): Subtitle
    {
        return $this->subtitleStreams[$this->selectedSubtitleStreamId];
    }

    /**
     * @return bool
     */
    public function hasAudio(): bool
    {
        return (bool) count($this->getAudioStreams());
    }

    /**
     * @return bool
     */
    public function hasVideo(): bool
    {
        return (bool) count($this->getVideoStreams());
    }

    /**
     * @return bool
     */
    public function hasSubtitle(): bool
    {
        return (bool) count($this->getSubtitleStreams());
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
}
