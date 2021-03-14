<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

class StringParameter extends AbstractParameter
{
    public function __construct(string $title)
    {
        parent::__construct($title, 'string');
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
            self::OPERATOR_SMALLER,
            self::OPERATOR_SMALLER_EQUAL,
            self::OPERATOR_BIGGER,
            self::OPERATOR_BIGGER_EQUAL,
        ];
    }
}
