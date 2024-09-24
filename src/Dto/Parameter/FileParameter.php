<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

class FileParameter extends AbstractParameter
{
    public function __construct(string $title, private readonly string $buttonText)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldFile');
    }

    protected function getTypeConfig(): array
    {
        return [
            'buttonText' => $this->buttonText,
        ];
    }

    public function getAllowedOperators(): array
    {
        return [];
    }
}
