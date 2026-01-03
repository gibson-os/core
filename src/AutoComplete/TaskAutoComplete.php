<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Repository\TaskRepository;
use Override;

class TaskAutoComplete implements AutoCompleteInterface
{
    public function __construct(private readonly TaskRepository $taskRepository)
    {
    }

    /**
     * @throws SelectError
     *
     * @return Task[]
     */
    #[Override]
    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->taskRepository->findByName(
            $namePart,
            isset($parameters['moduleId']) ? (int) $parameters['moduleId'] : null,
        );
    }

    /**
     * @throws SelectError
     */
    #[Override]
    public function getById(string $id, array $parameters): Task
    {
        return $this->taskRepository->getById((int) $id);
    }

    #[Override]
    public function getModel(): string
    {
        return 'GibsonOS.module.core.module.model.Task';
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
