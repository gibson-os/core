<?php
namespace GibsonOS\Core\Model;

interface ModelInterface
{
    /**
     * @return string
     */
    public static function getTableName(): string;
}