<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Store;

use GibsonOS\Core\Dto\Store\Filter\Option;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use JsonSerializable;
use MDO\Dto\Query\Where;

class Filter implements FilterInterface, JsonSerializable
{
    /**
     * @param Option[] $options
     */
    public function __construct(
        private readonly string $name,
        private readonly array $options,
        private readonly string $field,
        private readonly bool $multiple = true,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'options' => $this->getOptions(),
            'multiple' => $this->isMultiple(),
        ];
    }

    public function getWhere(string $field, array $value, DatabaseStoreWrapper $databaseStoreWrapper): Where
    {
        $fieldName = $this->getField();
        $where = sprintf('%s=:%s', $fieldName, $field);
        $parameters = [$field => reset($value)];

        if ($this->isMultiple()) {
            $where = sprintf(
                '%s IN (%s)',
                $fieldName,
                $databaseStoreWrapper->getSelectService()->getParametersString($value),
            );
            $parameters = $value;
        }

        return new Where($where, $parameters);
    }
}
