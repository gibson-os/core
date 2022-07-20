<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\AutoComplete\Weather;

use GibsonOS\Core\AutoComplete\Weather\LocationAutoComplete;
use GibsonOS\UnitTest\AutoComplete\AbstractAutoCompleteTest;

class LocationAutoCompleteTest extends AbstractAutoCompleteTest
{
    protected function getAutoCompleteClassName(): string
    {
        return LocationAutoComplete::class;
    }
}
