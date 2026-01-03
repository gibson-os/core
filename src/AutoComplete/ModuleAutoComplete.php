<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Module;
use GibsonOS\Core\Repository\ModuleRepository;
use Override;

class ModuleAutoComplete implements AutoCompleteInterface
{
    public function __construct(private readonly ModuleRepository $moduleRepository)
    {
    }

    /**
     * @throws SelectError
     *
     * @return Module[]
     */
    #[Override]
    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->moduleRepository->findByName($namePart);
    }

    /**
     * @throws SelectError
     */
    #[Override]
    public function getById(string $id, array $parameters): Module
    {
        return $this->moduleRepository->getById((int) $id);
    }

    #[Override]
    public function getModel(): string
    {
        return 'GibsonOS.module.core.module.model.Module';
    }

    #[Override]
    public function getValueField(): string
    {
        return 'id';
    }

    #[Override]
    public function getDisplayField(): string
    {
        return 'name';
    }
}
