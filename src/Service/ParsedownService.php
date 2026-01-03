<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use Override;
use Parsedown;

class ParsedownService extends Parsedown
{
    private ?string $linkTarget = '_blank';

    public function __construct()
    {
        $this->setSafeMode(true);
    }

    #[Override]
    protected function inlineLink($Excerpt): ?array
    {
        $link = parent::inlineLink($Excerpt);

        if ($this->linkTarget === null) {
            return $link;
        }

        if (!isset($link)) {
            return null;
        }

        if (isset($link['element']['attributes']['href'])) {
            $link['element']['attributes']['target'] = '_blank';
        }

        return $link;
    }

    public function setLinkTarget(?string $linkTarget): ParsedownService
    {
        $this->linkTarget = $linkTarget;

        return $this;
    }
}
