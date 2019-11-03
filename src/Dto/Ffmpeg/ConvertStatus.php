<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg;

use DateTime;

class ConvertStatus
{
    /**
     * @var int
     */
    private $frame;

    /**
     * @var int
     */
    private $frames = 0;

    /**
     * @var int
     */
    private $fps;

    /**
     * @var float
     */
    private $quality;

    /**
     * @var int;
     */
    private $size;

    /**
     * @var DateTime
     */
    private $time;

    /**
     * @var float
     */
    private $bitrate;

    /**
     * @return int
     */
    public function getFrame(): int
    {
        return $this->frame;
    }

    /**
     * @param int $frame
     *
     * @return ConvertStatus
     */
    public function setFrame(int $frame): ConvertStatus
    {
        $this->frame = $frame;

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
     * @return ConvertStatus
     */
    public function setFrames(int $frames): ConvertStatus
    {
        $this->frames = $frames;

        return $this;
    }

    /**
     * @return int
     */
    public function getFps(): int
    {
        return $this->fps;
    }

    /**
     * @param int $fps
     *
     * @return ConvertStatus
     */
    public function setFps(int $fps): ConvertStatus
    {
        $this->fps = $fps;

        return $this;
    }

    /**
     * @return float
     */
    public function getQuality(): float
    {
        return $this->quality;
    }

    /**
     * @param float $quality
     *
     * @return ConvertStatus
     */
    public function setQuality(float $quality): ConvertStatus
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return ConvertStatus
     */
    public function setSize(int $size): ConvertStatus
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getTime(): DateTime
    {
        return $this->time;
    }

    /**
     * @param DateTime $time
     *
     * @return ConvertStatus
     */
    public function setTime(DateTime $time): ConvertStatus
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return float
     */
    public function getBitrate(): float
    {
        return $this->bitrate;
    }

    /**
     * @param float $bitrate
     *
     * @return ConvertStatus
     */
    public function setBitrate(float $bitrate): ConvertStatus
    {
        $this->bitrate = $bitrate;

        return $this;
    }

    /**
     * @return int
     */
    public function getPercent(): int
    {
        if ($this->getFrames() === 0) {
            return 0;
        }

        $percent = intval((100 / $this->getFrames()) * $this->getFrame());

        return (int) ($percent > 100 ? 100 : $percent);
    }

    public function getTimeRemaining()
    {
        if ($this->getFrames() === 0) {
            return 0;
        }

        return new DateTime('@' . round(($this->getFrames() - $this->getFrame()) / $this->getFps()));
    }
}
