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

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'module';
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Module
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Module
     */
    public function setName(string $name): Module
    {
        $this->name = $name;

        return $this;
    }
}
