<?php
declare(strict_types=1);

namespace GibsonOS\Core\Mapper;

interface ObjectMapperInterface
{
    /**
     * @template T
     *
     * @param class-string<T>      $className
     * @param array<string, mixed> $properties
     *
     * @return T
     */
    public function mapToObject(string $className, array $properties): object;

    public function mapFromObject(object $object): int|float|string|bool|array|object|null;
}
