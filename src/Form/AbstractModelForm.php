<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

use GibsonOS\Core\Dto\Form;
use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Form\ModelFormConfig;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FormException;

abstract class AbstractModelForm
{
    private const POSSIBLE_PREFIXES = ['get', 'is', 'has', 'should'];

    /**
     * @return array<string, AbstractParameter>
     */
    abstract protected function getFields(ModelFormConfig $config): array;

    /**
     * @return array<string, Button>
     */
    abstract public function getButtons(ModelFormConfig $config): array;

    /**
     * @throws FormException
     */
    public function getForm(ModelFormConfig $config): Form
    {
        $fields = $this->getFields($config);

        if ($config->getModel() !== null) {
            foreach ($fields as $name => $field) {
                $this->setFieldValue($config, $field, $name);
            }
        }

        return new Form($fields, $this->getButtons($config));
    }

    /**
     * @throws FormException
     */
    private function setFieldValue(ModelFormConfig $config, AbstractParameter $field, string $name): void
    {
        $model = $config->getModel();

        if ($model === null) {
            return;
        }

        $propertyName = ucfirst($name);
        $getterPrefix = null;

        foreach (self::POSSIBLE_PREFIXES as $possiblePrefix) {
            if (method_exists($model, $possiblePrefix . $propertyName)) {
                $getterPrefix = $possiblePrefix;

                break;
            }
        }

        if ($getterPrefix === null) {
            throw new FormException(sprintf('No getter found for %s n %s!', $name, $model::class));
        }

        $field->setValue($model->{$getterPrefix . $propertyName}());
    }
}
