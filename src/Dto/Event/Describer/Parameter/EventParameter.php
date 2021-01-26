<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

use GibsonOS\Core\AutoComplete\EventAutoComplete;

class EventParameter extends AutoCompleteParameter
{
    private EventAutoComplete $eventAutoComplete;

    public function __construct(EventAutoComplete $eventAutoComplete, string $title = 'Event')
    {
        parent::__construct($title, $eventAutoComplete);
        $this->eventAutoComplete = $eventAutoComplete;
    }

    public function setOnlyActive(bool $onlyActive): EventParameter
    {
        $this->setParameter('onlyActive', $onlyActive);

        return $this;
    }
}
