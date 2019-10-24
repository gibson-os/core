<?php
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
    public function getFrame()
    {
        return $this->frame;
    }

    /**
     * @param int $frame
     * @return ConvertStatus
     */
    public function setFrame($frame)
    {
        $this->frame = $frame;
        return $this;
    }

    /**
     * @return int
     */
    public function getFrames()
    {
        return $this->frames;
    }

    /**
     * @param int $frames
     * @return ConvertStatus
     */
    public function setFrames($frames)
    {
        $this->frames = $frames;
        return $this;
    }

    /**
     * @return int
     */
    public function getFps()
    {
        return $this->fps;
    }

    /**
     * @param int $fps
     * @return ConvertStatus
     */
    public function setFps($fps)
    {
        $this->fps = $fps;
        return $this;
    }

    /**
     * @return float
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @param float $quality
     * @return ConvertStatus
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return ConvertStatus
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param DateTime $time
     * @return ConvertStatus
     */
    public function setTime(DateTime $time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return float
     */
    public function getBitrate()
    {
        return $this->bitrate;
    }

    /**
     * @param float $bitrate
     * @return ConvertStatus
     */
    public function setBitrate($bitrate)
    {
        $this->bitrate = $bitrate;
        return $this;
    }

    /**
     * @return int
     */
    public function getPercent()
    {
        if ($this->getFrames() === 0) {
            return 0;
        }

        $percent = intval((100/$this->getFrames()) * $this->getFrame());

        return $percent = $percent > 100 ? 100 : $percent;
    }

    public function getTimeRemaining()
    {
        if ($this->getFrames() === 0) {
            return 0;
        }


        return new DateTime('@' . round(($this->getFrames() - $this->getFrame()) / $this->getFps()));
    }
}