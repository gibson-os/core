<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

interface ModelInterface
{
    /**
     * @return string
     */
    public static function getTableName(): string;
}
