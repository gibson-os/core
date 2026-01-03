<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use Override;

class OptionParameter extends AbstractParameter
{
    public function __construct(string $title, private array $options)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldComboBox');
    }

    #[Override]
    protected function getTypeConfig(): array
    {
        return [
            'options' => $this->options,
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
