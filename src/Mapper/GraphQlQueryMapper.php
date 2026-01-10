<?php
declare(strict_types=1);

namespace GibsonOS\Core\Mapper;

class GraphQlQueryMapper
{
    public function mapToString(array $query): string
    {
        $queryString = '{' . PHP_EOL . "\t";

        foreach ($query as $key => $value) {
            $queryString .= is_string($key) ? $key : $value;

            if (is_array($value)) {
                $queryString .= ' ' . $this->mapToString($value);
            }

            $queryString .= PHP_EOL;
        }

        return $queryString . '}';
    }
}
