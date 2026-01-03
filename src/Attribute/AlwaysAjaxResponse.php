<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\AlwaysAjaxResponseService;
use Override;

#[Attribute(Attribute::TARGET_METHOD)]
class AlwaysAjaxResponse implements AttributeInterface
{
    #[Override]
    public function getAttributeServiceName(): string
    {
        return AlwaysAjaxResponseService::class;
    }
}
