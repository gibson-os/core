<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use Override;
use stdClass;

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

    public function setParameter(string $key, bool $value): AutoCompleteParameter
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    #[Override]
    protected function getTypeConfig(): array
    {
        return [
            'autoCompleteClassname' => $this->autoComplete::class,
            'model' => $this->autoComplete->getModel(),
            'valueField' => $this->autoComplete->getValueField(),
            'displayField' => $this->autoComplete->getDisplayField(),
            'parameters' => $this->getParameters() ?: new stdClass(),
        ];
    }

    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
        ];
    }
}
