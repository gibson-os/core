<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Action;
use GibsonOS\Core\Repository\ActionRepository;

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
    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->actionRepository->findByName(
            $namePart,
            isset($parameters['taskId']) ? (int) $parameters['taskId'] : null
        );
    }

    /**
     * @throws SelectError
     */
    public function getById(string $id, array $parameters): Action
    {
        return $this->actionRepository->getById((int) $id);
    }

    public function getModel(): string
    {
        return 'GibsonOS.module.core.module.model.Action';
    }

    public function getValueField(): string
    {
        return 'id';
    }

    public function getDisplayField(): string
    {
        return 'name';
    }
}
