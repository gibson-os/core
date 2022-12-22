<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use GibsonOS\Core\AutoComplete\ActionAutoComplete;

class ActionParameter extends AutoCompleteParameter
{
    public function __construct(ActionAutoComplete $actionAutoComplete, string $title = 'Aktion')
    {
        parent::__construct($title, $actionAutoComplete);
    }
}
