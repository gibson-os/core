<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use Override;

class BoolParameter extends AbstractParameter
{
    public function __construct(string $title)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldCheckbox');
    }

    #[Override]
    protected function getTypeConfig(): array
    {
        return [];
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
