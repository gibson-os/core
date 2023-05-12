<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store;

use GibsonOS\Core\Model\Action;

/**
 * @extends AbstractDatabaseStore<Action>
 */
class ActionStore extends AbstractDatabaseStore
{
    private ?int $taskId = null;

    protected function getModelClassName(): string
    {
        return Action::class;
    }

    protected function getDefaultOrder(): string
    {
        return '`name`';
    }

    protected function setWheres(): void
    {
        if ($this->taskId !== null) {
            $this->addWhere('`task_id`=?', [$this->taskId]);
        }
    }

    public function getList(): iterable
    {
        /** @var Action $action */
        foreach (parent::getList() as $action) {
            $data = $action->jsonSerialize();
            $data['id'] = 'a' . $data['id'];
            $data['leaf'] = true;

            yield $data;
        }
    }

    public function setTaskId(?int $taskId): ActionStore
    {
        $this->taskId = $taskId;

        return $this;
    }
}
