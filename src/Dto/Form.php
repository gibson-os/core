<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto;

use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use JsonSerializable;

class Form implements JsonSerializable
{
    /**
     * @param array<string, AbstractParameter> $fields
     * @param array<string, Button>            $buttons
     */
    public function __construct(
        private array $fields,
        private array $buttons,
    ) {
    }

    /**
     * @return array<string, AbstractParameter>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array<string, AbstractParameter> $fields
     *
     * @return $this
     */
    public function setFields(array $fields): Form
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return array<string, Button>
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * @param array<string, Button> $buttons
     */
    public function setButtons(array $buttons): Form
    {
        $this->buttons = $buttons;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'fields' => $this->getFields(),
            'buttons' => $this->getButtons(),
        ];
    }
}
