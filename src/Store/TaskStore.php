<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Task;
use JsonException;
use MDO\Enum\OrderDirection;
use ReflectionException;

/**
 * @extends AbstractDatabaseStore<Task>
 */
class TaskStore extends AbstractDatabaseStore
{
    private ?int $moduleId = null;

    protected function getModelClassName(): string
    {
        return Task::class;
    }

    protected function setWheres(): void
    {
        if ($this->moduleId !== null) {
            $this->addWhere('`module_id`=?', [$this->moduleId]);
        }
    }

    protected function getDefaultOrder(): array
    {
        return ['`name`' => OrderDirection::ASC];
    }

    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     *
     * @return iterable<array>
     */
    public function getList(): iterable
    {
        /** @var Task $task */
        foreach (parent::getList() as $task) {
            $data = $task->jsonSerialize();
            $data['id'] = 't' . $data['id'];

            yield $data;
        }
    }

    public function setModuleId(?int $moduleId): TaskStore
    {
        $this->moduleId = $moduleId;

        return $this;
    }
}
