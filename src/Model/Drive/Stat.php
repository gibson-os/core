<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model\Drive;

use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\Drive;
use mysqlDatabase;

/**
 * @method Drive getDrive()
 */
#[Table('system_drive_stat')]
class Stat extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $driveId;

    #[Column(length: 4)]
    private string $disk;

    #[Column(type: Column::TYPE_TIMESTAMP, default: Column::DEFAULT_CURRENT_TIMESTAMP)]
    private DateTimeInterface $added;

    #[Constraint]
    protected Drive $drive;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->added = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Stat
    {
        $this->id = $id;

        return $this;
    }

    public function getDriveId(): int
    {
        return $this->driveId;
    }

    public function setDriveId(int $driveId): Stat
    {
        $this->driveId = $driveId;

        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function setDisk(string $disk): Stat
    {
        $this->disk = $disk;

        return $this;
    }

    public function getAdded(): DateTimeImmutable|DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeImmutable|DateTimeInterface $added): Stat
    {
        $this->added = $added;

        return $this;
    }

    public function setDrive(Drive $drive): Stat
    {
        $this->drive = $drive;
        $this->setDriveId($drive->getId() ?? 0);

        return $this;
    }
}
