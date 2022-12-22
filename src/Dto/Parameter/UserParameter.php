<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter;

use GibsonOS\Core\AutoComplete\UserAutoComplete;

class UserParameter extends AutoCompleteParameter
{
    public function __construct(UserAutoComplete $userAutoComplete, string $title = 'Benutzer')
    {
        parent::__construct($title, $userAutoComplete);
    }
}
