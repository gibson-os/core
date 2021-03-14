<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Parameter\Weather;

use GibsonOS\Core\AutoComplete\Weather\LocationAutoComplete;
use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;

class LocationParameter extends AutoCompleteParameter
{
    private LocationAutoComplete $locationAutoComplete;

    public function __construct(LocationAutoComplete $locationAutoComplete, string $title = 'Standort')
    {
        parent::__construct($title, $locationAutoComplete);
        $this->locationAutoComplete = $locationAutoComplete;
    }

    public function setOnlyActive(bool $onlyActive): LocationParameter
    {
        $this->setParameter('onlyActive', $onlyActive);

        return $this;
    }
}
