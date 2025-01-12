<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

use GibsonOS\Core\Dto\Form;
use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Form\ModelFormConfig;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FormException;
use GibsonOS\Core\Model\ModelInterface;

/**
 * @template T of ModelInterface
 */
abstract class AbstractModelForm
{
    private const POSSIBLE_PREFIXES = ['get', 'is', 'has', 'should'];

    /**
     * @param ModelFormConfig<T|null> $config
     *
     * @return array<string, AbstractParameter>
     */
    abstract protected function getFields(ModelFormConfig $config): array;

    /**
     * @param ModelFormConfig<T|null> $config
     *
     * @return array<string, Button>
     */
    abstract protected function getButtons(ModelFormConfig $config): array;

    /**
     * @return class-string<T>
     */
    abstract protected function supportedModel(): string;

    /**
     * @param ModelFormConfig<T|null> $config
     *
     * @throws FormException
     */
    public function getForm(ModelFormConfig $config): Form
    {
        $fields = $this->getFields($config);
        $model = $config->getModel();

        if ($model instanceof ModelInterface) {
            if ($model::class !== $this->supportedModel() && !is_subclass_of($model, $this->supportedModel())) {
                throw new FormException(sprintf(
                    'Model "%s" is not supported by "%s". Supported is "%s"',
                    $model::class,
                    $this::class,
                    $this->supportedModel(),
                ));
            }

            foreach ($fields as $name => $field) {
                $this->setFieldValue($config, $field, $name);
            }
        }

        return new Form($fields, $this->getButtons($config));
    }

    /**
     * @param ModelFormConfig<T|null> $config
     *
     * @throws FormException
     */
    private function setFieldValue(ModelFormConfig $config, AbstractParameter $field, string $name): void
    {
        $model = $config->getModel();

        if (!$model instanceof ModelInterface) {
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
