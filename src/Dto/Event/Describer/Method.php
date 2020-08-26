<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer;

use GibsonOS\Core\Dto\Event\Describer\Parameter\AbstractParameter;

class Method
{
    /**
     * @var AbstractParameter[]
     */
    private $parameters = [];

    /**
     * @var AbstractParameter[]
     */
    private $returns = [];

    /**
     * @var string
     */
    private $title;

    /**
     * Method constructor.
     */
    public function __construct(string $title)
    {
        $this->title = $title;
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
    public function setParameters(array $parameters): Method
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return AbstractParameter[]
     */
    public function getReturns(): array
    {
        return $this->returns;
    }

    /**
     * @param AbstractParameter[] $returns
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
