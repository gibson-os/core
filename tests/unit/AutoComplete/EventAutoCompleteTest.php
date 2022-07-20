<?php
declare(strict_types=1);

namespace GibsonOS\UnitTest\AutoComplete;

use GibsonOS\Core\AutoComplete\EventAutoComplete;

class EventAutoCompleteTest extends AbstractAutoCompleteTest
{
    protected function getAutoCompleteClassName(): string
    {
        return EventAutoComplete::class;
    }
}
