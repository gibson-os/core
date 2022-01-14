<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

interface ModelInterface
{
    public function getTableName(): string;
}
