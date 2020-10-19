<?php
declare(strict_types=1);

namespace GibsonOS\Core\Event\AutoComplete;

use GibsonOS\Core\Model\ModelInterface;

interface AutoCompleteInterface
{
    public function getByNamePart(string $namePart): ModelInterface;

    public function getById($id): ModelInterface;

    public function getModel(): string;

    public function getParameters(): array;
}
