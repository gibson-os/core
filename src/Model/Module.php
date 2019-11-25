<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

class Module extends AbstractModel
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    public static function getTableName(): string
    {
        return 'module';
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Module
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Module
    {
        $this->name = $name;

        return $this;
    }
}
