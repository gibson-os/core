<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

class Lock extends AbstractModel
{
    /**
     * @var int
     */
    private $pid;

    /**
     * @var string
     */
    private $name;

    public static function getTableName(): string
    {
        return 'lock';
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Lock
    {
        $this->name = $name;

        return $this;
    }
}
