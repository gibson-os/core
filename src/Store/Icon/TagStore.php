<?php
declare(strict_types=1);

namespace GibsonOS\Core\Store\Icon;

use GibsonOS\Core\Model\Icon\Tag;
use GibsonOS\Core\Store\AbstractDatabaseStore;

class TagStore extends AbstractDatabaseStore
{
    protected function getModelClassName(): string
    {
        return Tag::class;
    }

    protected function getCountField(): string
    {
        return '*';
    }

    protected function getDefaultOrder(): string
    {
        return '`tag`';
    }
}
