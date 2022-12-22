<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use GibsonOS\Core\AutoComplete\ModuleAutoComplete;

class ModuleParameter extends AutoCompleteParameter
{
    public function __construct(ModuleAutoComplete $moduleAutoComplete, string $title = 'Modul')
    {
        parent::__construct($title, $moduleAutoComplete);
    }
}
