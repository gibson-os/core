<?php
declare(strict_types=1);

namespace GibsonOS\Core\Dto\Event\Describer\Parameter;

abstract class AbstractParameter
{
    private string $title;

    private string $type;

    private array $listeners = [];

    abstract protected function getTypeConfig(): array;

    public function __construct(string $title, string $type)
    {
        $this->title = $title;
        $this->type = $type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getConfig(): array
    {
        return array_merge([
            'listeners' => $this->listeners,
        ], $this->getTypeConfig());
    }

    public function setListener(string $field, array $options): void
    {
        $this->listeners[$field] = $options;
    }
}
