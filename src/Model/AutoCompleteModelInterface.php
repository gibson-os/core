<?php
declare(strict_types=1);

namespace GibsonOS\Core\Model;

interface AutoCompleteModelInterface
{
    /**
     * @return string|int|float
     */
    public function getAutoCompleteId();
}
