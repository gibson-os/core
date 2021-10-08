<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer;

use GibsonOS\Core\Dto\Parameter\AbstractParameter;

class Method
{
    /**
     * @var array<string, AbstractParameter>
     */
    private array $parameters = [];

    /**
     * @var array<string, AbstractParameter>
     */
    private array $returns = [];

    public function __construct(private string $title)
    {
    }

    /**
     * @return array<string, AbstractParameter>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, AbstractParameter> $parameters
     */
    public function setParameters(array $parameters): Method
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return array<string, AbstractParameter>
     */
    public function getReturns(): array
    {
        return $this->returns;
    }

    /**
     * @param array<string, AbstractParameter> $returns
     */
    public function setReturns(array $returns): Method
    {
        $this->returns = $returns;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
