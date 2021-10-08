<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

interface AutoCompleteModelInterface
{
    public function getAutoCompleteId(): string|int|float;
}
