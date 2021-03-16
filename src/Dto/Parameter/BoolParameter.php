<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

class BoolParameter extends AbstractParameter
{
    public function __construct(string $title)
    {
        parent::__construct($title, 'gosFormCheckbox');
    }

    protected function getTypeConfig(): array
    {
        return [];
    }

    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
        ];
    }
}
