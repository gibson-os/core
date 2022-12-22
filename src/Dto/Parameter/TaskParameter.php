<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use GibsonOS\Core\AutoComplete\TaskAutoComplete;

class TaskParameter extends AutoCompleteParameter
{
    public function __construct(TaskAutoComplete $taskAutoComplete, string $title = 'Task')
    {
        parent::__construct($title, $taskAutoComplete);
    }
}
