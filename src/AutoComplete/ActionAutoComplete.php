<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Repository\ActionRepository;
use Override;

class ActionAutoComplete implements AutoCompleteInterface
{
    public function __construct(private readonly ActionRepository $actionRepository)
    {
    }

    /**
     * @throws SelectError
     *
     * @return Action[]
     */
    #[Override]
    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->actionRepository->findByName(
            $namePart,
            isset($parameters['taskId']) ? (int) $parameters['taskId'] : null,
        );
    }

    /**
     * @throws SelectError
     */
    #[Override]
    public function getById(string $id, array $parameters): Action
    {
        return $this->actionRepository->getById((int) $id);
    }

    #[Override]
    public function getModel(): string
    {
        return 'GibsonOS.module.core.module.model.Action';
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
