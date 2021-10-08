<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use GibsonOS\Core\AutoComplete\AutoCompleteInterface;

class AutoCompleteParameter extends AbstractParameter
{
    private array $parameters = [];

    public function __construct(string $title, private AutoCompleteInterface $autoComplete)
    {
        parent::__construct($title, 'gosModuleCoreParameterTypeAutoComplete');
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
            'autoCompleteClassname' => $this->autoComplete::class,
            'model' => $this->autoComplete->getModel(),
            'parameters' => $this->getParameters(),
        ];
    }

    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
        ];
    }
}
