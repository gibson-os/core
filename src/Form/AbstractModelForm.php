<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FormException;
use GibsonOS\Core\Model\ModelInterface;

abstract class AbstractModelForm extends AbstractForm
{
    private const POSSIBLE_PREFIXES = ['get', 'is', 'has', 'should'];

    private ?ModelInterface $model = null;

    /**
     * @return array<string, Button>
     */
    abstract public function getButtons(): array;

    public function getModel(): ?ModelInterface
    {
        return $this->model;
    }

    public function setModel(?ModelInterface $model): AbstractModelForm
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @throws FormException
     */
    public function getForm(): array
    {
        $fields = $this->getFields();

        if ($this->model !== null) {
            foreach ($fields as $name => $field) {
                $this->setFieldValue($field, $name);
            }
        }

        return [
            'fields' => $fields,
            'buttons' => $this->getButtons(),
        ];
    }

    /**
     * @throws FormException
     */
    private function setFieldValue(AbstractParameter $field, string $name): void
    {
        if ($this->model === null) {
            return;
        }

        $propertyName = ucfirst($name);
        $getterPrefix = null;

        foreach (self::POSSIBLE_PREFIXES as $possiblePrefix) {
            if (method_exists($this->model, $possiblePrefix . $propertyName)) {
                $getterPrefix = $possiblePrefix;

                break;
            }
        }

        if ($getterPrefix === null) {
            throw new FormException(sprintf('No getter found for %s n %s!', $name, $this->model::class));
        }

        $field->setValue($this->model->{$getterPrefix . $propertyName}());
    }
}
