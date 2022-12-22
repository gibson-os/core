<?php
declare(strict_types=1);

namespace GibsonOS\Core\AutoComplete;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Task;
use GibsonOS\Core\Repository\TaskRepository;

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
    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->taskRepository->findByName($namePart, $parameters['moduleId'] ?? null);
    }

    /**
     * @throws SelectError
     */
    public function getById(string $id, array $parameters): Task
    {
        return $this->taskRepository->getById((int) $id);
    }

    public function getModel(): string
    {
        return 'GibsonOS.module.core.task.model.Task';
    }
}
