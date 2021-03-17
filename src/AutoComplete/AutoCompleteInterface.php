<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Model\AutoCompleteModelInterface;

interface AutoCompleteInterface
{
    /**
     * @return AutoCompleteModelInterface[]
     */
    public function getByNamePart(string $namePart, array $parameters): array;

    public function getById($id, array $parameters): AutoCompleteModelInterface;

    public function getModel(): string;
}
