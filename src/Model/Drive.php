<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use mysqlDatabase;

class Drive extends AbstractModel implements JsonSerializable
{
    public const LBA_YES = 'yes';

    public const LBA_NO = 'no';

    private ?int $id = null;

    private string $serial;

    private string $model;

    private string $fwRev;

    private string $config;

    private string $rawChs;

    private int $trackSize;

    private int $sectSize;

    private int $eccBytes;

    private string $buffType;

    private int $buffSize;

    private int $maxMultSect;

    private int $multSect;

    private string $curChs;

    private int $curSects;

    private string $lba;

    private int $lbaSects;

    private string $ioRdy;

    private string $tPio;

    private string $tDma;

    private DateTimeInterface $added;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->added = new DateTimeImmutable();
    }

    public static function getTableName(): string
    {
        return 'system_drive';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Drive
    {
        $this->id = $id;

        return $this;
    }

    public function getSerial(): string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): Drive
    {
        $this->serial = $serial;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): Drive
    {
        $this->model = $model;

        return $this;
    }

    public function getFwRev(): string
    {
        return $this->fwRev;
    }

    public function setFwRev(string $fwRev): Drive
    {
        $this->fwRev = $fwRev;

        return $this;
    }

    public function getConfig(): string
    {
        return $this->config;
    }

    public function setConfig(string $config): Drive
    {
        $this->config = $config;

        return $this;
    }

    public function getRawChs(): string
    {
        return $this->rawChs;
    }

    public function setRawChs(string $rawChs): Drive
    {
        $this->rawChs = $rawChs;

        return $this;
    }

    public function getTrackSize(): int
    {
        return $this->trackSize;
    }

    public function setTrackSize(int $trackSize): Drive
    {
        $this->trackSize = $trackSize;

        return $this;
    }

    public function getSectSize(): int
    {
        return $this->sectSize;
    }

    public function setSectSize(int $sectSize): Drive
    {
        $this->sectSize = $sectSize;

        return $this;
    }

    public function getEccBytes(): int
    {
        return $this->eccBytes;
    }

    public function setEccBytes(int $eccBytes): Drive
    {
        $this->eccBytes = $eccBytes;

        return $this;
    }

    public function getBuffType(): string
    {
        return $this->buffType;
    }

    public function setBuffType(string $buffType): Drive
    {
        $this->buffType = $buffType;

        return $this;
    }

    public function getBuffSize(): int
    {
        return $this->buffSize;
    }

    public function setBuffSize(int $buffSize): Drive
    {
        $this->buffSize = $buffSize;

        return $this;
    }

    public function getMaxMultSect(): int
    {
        return $this->maxMultSect;
    }

    public function setMaxMultSect(int $maxMultSect): Drive
    {
        $this->maxMultSect = $maxMultSect;

        return $this;
    }

    public function getMultSect(): int
    {
        return $this->multSect;
    }

    public function setMultSect(int $multSect): Drive
    {
        $this->multSect = $multSect;

        return $this;
    }

    public function getCurChs(): string
    {
        return $this->curChs;
    }

    public function setCurChs(string $curChs): Drive
    {
        $this->curChs = $curChs;

        return $this;
    }

    public function getCurSects(): int
    {
        return $this->curSects;
    }

    public function setCurSects(int $curSects): Drive
    {
        $this->curSects = $curSects;

        return $this;
    }

    public function getLba(): string
    {
        return $this->lba;
    }

    public function setLba(string $lba): Drive
    {
        $this->lba = $lba;

        return $this;
    }

    public function getLbaSects(): int
    {
        return $this->lbaSects;
    }

    public function setLbaSects(int $lbaSects): Drive
    {
        $this->lbaSects = $lbaSects;

        return $this;
    }

    public function getIoRdy(): string
    {
        return $this->ioRdy;
    }

    public function setIoRdy(string $ioRdy): Drive
    {
        $this->ioRdy = $ioRdy;

        return $this;
    }

    public function getTPio(): string
    {
        return $this->tPio;
    }

    public function setTPio(string $tPio): Drive
    {
        $this->tPio = $tPio;

        return $this;
    }

    public function getTDma(): string
    {
        return $this->tDma;
    }

    public function setTDma(string $tDma): Drive
    {
        $this->tDma = $tDma;

        return $this;
    }

    public function getAdded(): DateTimeImmutable|DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeImmutable|DateTimeInterface $added): Drive
    {
        $this->added = $added;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'added' => $this->getAdded()->format('Y-m-d H:i:s'),
            'bufferSize' => $this->getBuffSize(),
            'bufferType' => $this->getBuffType(),
            'config' => $this->getConfig(),
            'curChs' => $this->getCurChs(),
            'curSects' => $this->getCurSects(),
            'eccBytes' => $this->getEccBytes(),
            'fwRev' => $this->getFwRev(),
            'ioRdy' => $this->getIoRdy(),
            'lba' => $this->getLba(),
            'lbaSects' => $this->getLbaSects(),
            'maxMultSect' => $this->getMaxMultSect(),
            'model' => $this->getModel(),
            'multSect' => $this->getMultSect(),
            'rawChs' => $this->getRawChs(),
            'sectSize' => $this->getSectSize(),
            'serial' => $this->getSerial(),
            'tDma' => $this->getTDma(),
            'tPio' => $this->getTPio(),
            'trackSize' => $this->getTrackSize(),
        ];
    }
}
