<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

use GibsonOS\Core\Dto\Form;
use GibsonOS\Core\Dto\Form\AbstractModelConfig;
use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FormException;
use GibsonOS\Core\Model\ModelInterface;

abstract class AbstractModelForm
{
    private const POSSIBLE_PREFIXES = ['get', 'is', 'has', 'should'];

    private ?ModelInterface $model = null;

    /**
     * @return array<string, AbstractParameter>
     */
    abstract protected function getFields(AbstractModelConfig $config = null): array;

    /**
     * @return array<string, Button>
     */
    abstract public function getButtons(AbstractModelConfig $config = null): array;

    /**
     * @throws FormException
     */
    public function getForm(AbstractModelConfig $config = null): Form
    {
        $fields = $this->getFields($config);

        if ($this->model !== null) {
            foreach ($fields as $name => $field) {
                $this->setFieldValue($field, $name);
            }
        }

        return new Form($fields, $this->getButtons($config));
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
