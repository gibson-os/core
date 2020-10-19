<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

use GibsonOS\Core\Event\AutoComplete\AutoCompleteInterface;

class AutoCompleteParameter extends AbstractParameter
{
    /**
     * @var AutoCompleteInterface
     */
    private $autoComplete;

    public function __construct(string $title, AutoCompleteInterface $autoComplete)
    {
        parent::__construct($title, 'autoComplete');
        $this->autoComplete = $autoComplete;
    }

    protected function getTypeConfig(): array
    {
        return [
            'model' => $this->autoComplete->getModel(),
            'parameters' => $this->autoComplete->getParameters(),
        ];
    }
}
