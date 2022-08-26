<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

class StringParameter extends AbstractParameter
{
    public const INPUT_TYPE_TEXT = 'text';

    public const INPUT_TYPE_PASSWORD = 'password';

    public const INPUT_TYPE_URL = 'url';

    public const INPUT_TYPE_EMAIL = 'email';

    private string $inputType = self::INPUT_TYPE_TEXT;

    public function __construct(string $title)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldTextField');
    }

    protected function getTypeConfig(): array
    {
        return ['inputType' => $this->inputType];
    }

    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
        ];
    }

    public function setInputType(string $inputType): StringParameter
    {
        $this->inputType = $inputType;

        return $this;
    }
}
