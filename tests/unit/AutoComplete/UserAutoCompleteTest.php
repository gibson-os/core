<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\AutoComplete;

use GibsonOS\Core\AutoComplete\UserAutoComplete;

class UserAutoCompleteTest extends AbstractAutoCompleteTest
{
    protected function getAutoCompleteClassName(): string
    {
        return UserAutoComplete::class;
    }
}
