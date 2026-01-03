<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use Override;

class FileParameter extends AbstractParameter
{
    public function __construct(string $title, private readonly string $buttonText)
    {
        parent::__construct($title, 'gosCoreComponentFormFieldFile');
    }

    #[Override]
    protected function getTypeConfig(): array
    {
        return [
            'buttonText' => $this->buttonText,
        ];
    }

    #[Override]
    public function getAllowedOperators(): array
    {
        return [];
    }
}
