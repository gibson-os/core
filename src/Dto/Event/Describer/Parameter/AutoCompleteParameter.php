<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

use GibsonOS\Core\AutoComplete\AutoCompleteInterface;

class AutoCompleteParameter extends AbstractParameter
{
    private AutoCompleteInterface $autoComplete;

    private array $parameters = [];

    public function __construct(string $title, AutoCompleteInterface $autoComplete)
    {
        parent::__construct($title, 'autoComplete');
        $this->autoComplete = $autoComplete;
    }

    public function getAutoComplete(): AutoCompleteInterface
    {
        return $this->autoComplete;
    }

    public function setParameters(array $parameters): AutoCompleteParameter
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function setParameter(string $key, $value): AutoCompleteParameter
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    protected function getTypeConfig(): array
    {
        return [
            'autoCompleteClassname' => get_class($this->autoComplete),
            'model' => $this->autoComplete->getModel(),
            'parameters' => $this->getParameters(),
        ];
    }
}
