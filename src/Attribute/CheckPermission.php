<?php
declare(strict_types=1);

namespace GibsonOS\Core\Attribute;

use Attribute;
use GibsonOS\Core\Service\Attribute\PermissionAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CheckPermission implements AttributeInterface
{
    public function __construct(private int $permission)
    {
    }

    public function getPermission(): int
    {
        return $this->permission;
    }

    public function getAttributeServiceName(): string
    {
        return PermissionAttribute::class;
    }
}