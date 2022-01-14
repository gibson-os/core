<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Table;

#[Table(engine: 'MEMORY')]
class Lock extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $pid;

    #[Column(length: 255, primary: true)]
    private string $name;

    #[Column]
    private bool $stop = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Lock
    {
        $this->name = $name;

        return $this;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function setPid(int $pid): Lock
    {
        $this->pid = $pid;

        return $this;
    }

    public function shouldStop(): bool
    {
        return $this->stop;
    }

    public function setStop(bool $stop): Lock
    {
        $this->stop = $stop;

        return $this;
    }
}
