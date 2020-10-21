<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Model\ModelInterface;

interface AutoCompleteInterface
{
    /**
     * @return ModelInterface[]
     */
    public function getByNamePart(string $namePart, array $parameters): array;

    public function getById($id, array $parameters): ModelInterface;

    public function getModel(): string;
}
