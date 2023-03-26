<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Ffmpeg;

use DateTime;
use DateTimeInterface;
use Exception;
use GibsonOS\Core\Exception\SetError;
use JsonSerializable;

class ConvertStatus implements JsonSerializable
{
    public const STATUS_ERROR = 'error';

    public const STATUS_WAIT = 'wait';

    public const STATUS_GENERATE = 'generate';

    public const STATUS_GENERATED = 'generated';

    private const STATUS = [
        self::STATUS_ERROR,
        self::STATUS_WAIT,
        self::STATUS_GENERATE,
        self::STATUS_GENERATED,
    ];

    private string $status;

    private int $frame;

    private int $frames = 0;

    private int $fps;

    private float $quality;

    private int $size;

    private DateTimeInterface $time;

    private float $bitrate;

    /**
     * ConvertStatus constructor.
     *
     * @throws SetError
     */
    public function __construct(string $status)
    {
        $this->setStatus($status);
    }

    public function getFrame(): int
    {
        return $this->frame;
    }

    public function setFrame(int $frame): ConvertStatus
    {
        $this->frame = $frame;

        return $this;
    }

    public function getFrames(): int
    {
        return $this->frames;
    }

    public function setFrames(int $frames): ConvertStatus
    {
        $this->frames = $frames;

        return $this;
    }

    public function getFps(): int
    {
        return $this->fps;
    }

    public function setFps(int $fps): ConvertStatus
    {
        $this->fps = $fps;

        return $this;
    }

    public function getQuality(): float
    {
        return $this->quality;
    }

    public function setQuality(float $quality): ConvertStatus
    {
        $this->quality = $quality;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): ConvertStatus
    {
        $this->size = $size;

        return $this;
    }

    public function getTime(): DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(DateTimeInterface $time): ConvertStatus
    {
        $this->time = $time;

        return $this;
    }

    public function getBitrate(): float
    {
        return $this->bitrate;
    }

    public function setBitrate(float $bitrate): ConvertStatus
    {
        $this->bitrate = $bitrate;

        return $this;
    }

    public function getPercent(): int
    {
        if ($this->getFrames() === 0) {
            return 0;
        }

        $percent = intval((100 / $this->getFrames()) * $this->getFrame());

        return $percent > 100 ? 100 : $percent;
    }

    /**
     * @throws Exception
     */
    public function getTimeRemaining(): ?DateTime
    {
        if ($this->getFrames() === 0) {
            return null;
        }

        return new DateTime('@' . round(($this->getFrames() - $this->getFrame()) / $this->getFps()));
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @throws SetError
     */
    public function setStatus(string $status): ConvertStatus
    {
        if (!in_array($status, self::STATUS)) {
            throw new SetError(sprintf(
                'Status "%s" nicht erlaubt! Erlaubt: %s',
                $status,
                implode(', ', self::STATUS)
            ));
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function jsonSerialize(): array
    {
        if ($this->status === self::STATUS_GENERATE) {
            $timeRemaining = $this->getTimeRemaining();

            return [
                'status' => $this->getStatus(),
                'bitrate' => $this->getBitrate(),
                'fps' => $this->getFps(),
                'frame' => $this->getFrame(),
                'frames' => $this->getFrames(),
                'quality' => $this->getQuality(),
                'size' => $this->getSize(),
                'time' => $this->getTime()->format('H:i:s'),
                'timeRemaining' => $timeRemaining instanceof DateTime ? $timeRemaining->format('H:i:s') : 0,
                'percent' => $this->getPercent(),
            ];
        }

        return ['status' => $this->getStatus()];
    }
}
