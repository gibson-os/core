<?php
declare(strict_types=1);

namespace GibsonOS\Core\Form;

use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\FormException;
use GibsonOS\Core\Exception\MapperException;
use GibsonOS\Core\Model\ModelInterface;
use JsonException;
use ReflectionException;

/**
 * @template T of ModelInterface
 */
abstract class AbstractModelForm extends AbstractForm
{
    private const POSSIBLE_PREFIXES = ['get', 'is', 'has', 'should'];

    private ?ModelInterface $model = null;

    /**
     * @return class-string<T>
     */
    abstract protected function getModelClassName(): string;

    /**
     * @param T $data
     *
     * @throws FormException
     */
    public function setData(ModelInterface $data): void
    {
        $modelClassName = $this->getModelClassName();

        if ($data::class !== $modelClassName) {
            throw new FormException(sprintf('Data %s is no instance of %s!', $data::class, $modelClassName));
        }

        $this->model = $data;

        foreach ($this->fields as $name => $field) {
            $propertyName = ucfirst($name);
            $getterPrefix = null;

            foreach (self::POSSIBLE_PREFIXES as $possiblePrefix) {
                if (method_exists($data, $possiblePrefix . $propertyName)) {
                    $getterPrefix = $possiblePrefix;

                    break;
                }
            }

            if ($getterPrefix === null) {
                throw new FormException(sprintf('No getter found for %s n %s!', $name, $data::class));
            }

            $field->setValue($data->{$getterPrefix . $propertyName}());
        }
    }

    /**
     * @throws FactoryError
     * @throws MapperException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function getData(): ModelInterface
    {
        $modelClassName = $this->getModelClassName();
        $model = $this->model ?? new $modelClassName();

        $data = array_map(fn (AbstractParameter $field) => $field->getValue(), $this->fields);
        $this->modelMapper->setObjectValues($model, $data);

        return $model;
    }
}
