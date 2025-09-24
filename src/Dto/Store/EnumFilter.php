<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Store;

use GibsonOS\Core\Dto\Store\Filter\Option;
use GibsonOS\Core\Exception\FilterException;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use JsonSerializable;
use MDO\Dto\Query\Where;

class EnumFilter implements FilterInterface, JsonSerializable
{
    private array $options = [];

    /**
     * @param class-string $enumClassName
     *
     * @throws FilterException
     */
    public function __construct(
        private readonly string $name,
        private readonly string $enumClassName,
    ) {
        if (!enum_exists($this->enumClassName)) {
            throw new FilterException(sprintf('Enum %d not found', $this->enumClassName));
        }

        foreach ($this->enumClassName::cases() as $value) {
            $this->options[] = new Option($value->value, $value->name);
        }
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

    public function getWhere(string $field, array $values, DatabaseStoreWrapper $databaseStoreWrapper): Where
    {
        $conditions = [];
        $parameters = [];

        foreach ($values as $value) {
            $key = sprintf('filter%s%s', $field, $value);
            $conditions[] = sprintf('(`%s`=:%s)', $field, $key);
            $parameters[$key] = $value;
        }

        if (count($conditions) === 0) {
            $conditions[] = '1';
        }

        return new Where(implode(' OR ', $conditions), $parameters);
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'options' => $this->getOptions(),
            'multiple' => true,
        ];
    }
}
