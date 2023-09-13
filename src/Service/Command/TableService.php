<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Command;

class TableService
{
    /**
     * @param string[]   $headers
     * @param string[][] $content
     */
    public function getTable(array $headers, array $content): string
    {
        $columnsLength = $this->getColumnsLength($headers, $content);
        $table =
            $this->getRow($headers, $columnsLength) .
            str_pad('', array_sum($columnsLength) + ((count($columnsLength) - 1) * 3), '-') . PHP_EOL
        ;

        foreach ($content as $contentRow) {
            $table .= $this->getRow($contentRow, $columnsLength);
        }

        return $table;
    }

    /**
     * @param string[]   $headers
     * @param string[][] $content
     *
     * @return int[]
     */
    private function getColumnsLength(array $headers, array $content): array
    {
        $columnsLength = array_map(fn (string $header): int => mb_strlen($header), $headers);

        foreach ($content as $contentRow) {
            foreach ($columnsLength as $column => &$columnLength) {
                $columnLength = max($columnLength, mb_strlen($contentRow[$column] ?? ''));
            }
        }

        return $columnsLength;
    }

    private function getRow(array $content, array $columnsLength): string
    {
        return sprintf(implode(' | ', array_map(fn (int $length): string => '%-' . $length . 's', $columnsLength)), ...$content) . PHP_EOL;
    }
}
