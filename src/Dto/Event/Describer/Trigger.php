<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer;

use GibsonOS\Core\Dto\Event\Describer\Parameter\AbstractParameter;

class Trigger
{
    private string $title;

    /**
     * @var AbstractParameter[]
     */
    private array $parameters = [];

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return AbstractParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param AbstractParameter[] $parameters
     */
    public function setParameters(array $parameters): Trigger
    {
        $this->parameters = $parameters;

        return $this;
    }
}
